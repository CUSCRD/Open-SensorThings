<?php


namespace App\API\DataManipulation;


use App\Constant\TablesName;
use App\API\EntityGetter\MeasurementUnit;
use App\API\EntityGetter\MultiDataStream;
use App\API\EntityGetter\ObservationType;
use App\API\EntityGetter\ObservedProperty;
use App\API\Helpers\EntityPropertyGetter;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntityDeletion
{
    /**
     * @throws Exception
     */
    public static function delete(Request $request, $pathVariable = null): array
    {
        try {
            $analyze = EntityModification::handleModificationPath($pathVariable);
        } catch (Exception $exception) {
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
        DB::beginTransaction();
        try {
            $id = $analyze['id'];
            $id = array($id);
            $table = EntityPropertyGetter::getTables($analyze['entity']);
            if ($table != null) {
                //mặc dù có on delete cascade nhưng có nếu dữ nằm ở bảng trung gian (dạng nhiều - nhiều) thì
                //lúc xóa entity, dữ liệu ở bảng trung gian bị xóa, một số entity không mong muốn vẫn còn tồn tại
                //cần cài đặt xử lý riêng
                // ví dụ: xóa measurement unit thì datastream phải bị xóa theo
                // nhưng chỉ có measurement unit và bảng trung gian bị xóa, các datastream sử dụng measurement unit bị xóa
                // vẫn còn tồn tại
                switch ($analyze['entity']) {
                    case MeasurementUnit::PATH_VARIABLE_NAME:
                        static::deleteMeasurementUnit($id);
                        break;
                    case ObservationType::PATH_VARIABLE_NAME:
                        static::deleteObservationType($id);
                        break;
                    case ObservedProperty::PATH_VARIABLE_NAME:
                        static::observedProperty($id);
                        break;
                    case "encodingtypes":
                    default:
                        try {
                            static::deleteEntity($table, $id);
                        } catch (Exception $exception) {
                            throw new Exception('path param ' . $analyze['entity'] . ' is not supported', 405);
                        }
                        break;
                }
            }
            DB::commit();
            return [
                'data' => null,
                'code' => 200
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function deleteEntity(string $entityTable, array $id)
    {
        try {
            DB::table($entityTable)->delete($id);
        } catch (Exception $exception) {
            throw new Exception('cannot delete entity' . $entityTable, 500);
        }
    }

    protected static function deleteMeasurementUnit(array $id)
    {
        $mJoinName = MeasurementUnit::JOIN_NAME;
        $dsmu = "dsmu";
        $m_Table = MeasurementUnit::TABLE_NAME;
        $idDS_DB = DB::table($m_Table, $mJoinName)
            ->join(TablesName::DATA_STREAM_MEASUREMENT_UNIT . ' as ' . $dsmu, $mJoinName . ".id", '=', $dsmu . '.unitId')
            ->whereIn($mJoinName . '.id', $id)->get($dsmu . '.dataStreamId');
        $idDS = array_values(json_decode(json_encode($idDS_DB), true));
        static::deleteEntity(MultiDataStream::TABLE_NAME, $idDS);
        static::deleteEntity($m_Table, $id);
    }

    protected static function deleteObservationType(array $id)
    {
        $otJoinName = ObservationType::JOIN_NAME;
        $otTable = ObservationType::TABLE_NAME;
        $dsmot = 'dsmot';
        $idDS_DB = DB::table($otTable, $otJoinName)
            ->join(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE . ' as ' . $dsmot, $otJoinName . ".id", '=', $dsmot . '.observationType')
            ->whereIn($otJoinName . '.id', $id)->get($dsmot . '.dataStreamId');
        $idDS = array_values(json_decode(json_encode($idDS_DB), true));
        static::deleteEntity(MultiDataStream::TABLE_NAME, $idDS);
        static::deleteEntity($otTable, $id);
    }

    protected static function observedProperty(array $id)
    {
        $opJoinName = ObservedProperty::JOIN_NAME;
        $opTable = ObservedProperty::TABLE_NAME;
        $dsop = 'dsop';
        $idDS_DB = DB::table($opTable, $opJoinName)
            ->join(TablesName::DATA_STREAM_OBSERVED_PROPERTY . ' as ' . $dsop, $opJoinName . ".id", '=', $dsop . '.observedPropertyId')
            ->whereIn($opJoinName . '.id', $id)->get($dsop . '.dataStreamId');
        $idDS = array_values(json_decode(json_encode($idDS_DB), true));
        static::deleteEntity(MultiDataStream::TABLE_NAME, $idDS);
        static::deleteEntity($opTable, $id);
    }
}
