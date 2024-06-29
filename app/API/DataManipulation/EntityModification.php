<?php


namespace App\API\DataManipulation;


use App\Constant\TablesName;
use App\API\EntityGetter\MeasurementUnit;
use App\API\EntityGetter\MultiDataStream;
use App\API\EntityGetter\Observation;
use App\API\EntityGetter\ObservationType;
use App\API\EntityGetter\ObservedProperty;
use App\API\EntityGetter\Sensor;
use App\API\EntityGetter\TaskingCapabilities;
use App\API\EntityGetter\Thing;
use App\API\Helpers\EntityPathRequest;
use App\API\Helpers\EntityPropertyGetter;
use App\Http\Controllers\API\GetController;
use App\Http\Controllers\API\IoTController;
use Exception;
use Facade\Ignition\Tabs\Tab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntityModification
{
    /**
     * @throws Exception
     */
    public static function patch(Request $request, $pathVariable = null): array
    {
        try {
            $analyze = static::handleModificationPath($pathVariable);
        } catch (Exception $exception) {
            return [
                'data' => ['error' => $exception->getMessage()],
                'code' => 400
            ];
        }

        //của thing
        if (isset($request['properties'])) {
            $request['properties'] = json_encode($request['properties']);
        }

        //của observation
        if ($analyze['entity'] == Observation::PATH_VARIABLE_NAME) {
            if (isset($request['result'])) {
                $request['result'] = json_encode($request['result']);
            }
        }

        DB::beginTransaction();
        try {
            $id = $analyze['id'];
            $updateArray = $request->toArray();
            switch ($analyze['entity']) {
                case MeasurementUnit::PATH_VARIABLE_NAME:
                case Observation::PATH_VARIABLE_NAME:
                case ObservationType::PATH_VARIABLE_NAME:
                case ObservedProperty::PATH_VARIABLE_NAME:
                case Thing::PATH_VARIABLE_NAME:
                case 'encodingtypes':
                    break;
                    //các entity có thuộc tính dạng danh sách
                case MultiDataStream::PATH_VARIABLE_NAME:
                    $updateArray = static::updateDataStream($id, $updateArray);
                    break;
                case Sensor::PATH_VARIABLE_NAME:
                    $updateArray = static::updateSensor($updateArray);
                    break;
                case TaskingCapabilities::PATH_VARIABLE_NAME:
                    $updateArray = static::updateTaskingCap($updateArray, $id);
                    break;
                default:
                    throw new Exception('path param ' . $analyze['entity'] . ' is not supported', 405);
            }

            $entity = EntityPropertyGetter::getTables($analyze['entity']);
            if (count($updateArray) > 0) {
                if ($analyze['entity'] === 'taskingcapability')
                    static::updateTasking("task", $id, $updateArray);
                else
                    static::update($entity, $id, $updateArray);
            }
            DB::commit();
            //thành công
            return [
                'message' => 'update successfully',
                'code' => 200
            ];
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }
    public static function updateTasking(string $entityTable, int $id, array $update)
    {
        // dd(DB::table($entityTable)->where('thing_id', '=', $id)->get()->toArray());
        try {

            foreach ($update as $key => $value) {
                DB::table($entityTable)->where('id', '=', $value["id"])->update(["taskingParameters" => $value["taskingParameters"]]);
            }
        } catch (Exception $exception) {
            throw new Exception('cannot update entity ' . $entityTable, 406);
        }
    }
    public static function update(string $entityTable, int $id, array $update)
    {
        try {
            DB::table($entityTable)->where('id', '=', $id)->update($update);
        } catch (Exception $exception) {
            throw new Exception('cannot update entity ' . $entityTable, 406);
        }
    }

    //    {
    //    "name": "updated datastream",
    //    "description": "this datastream has been updated.",
    //    "Thing":{"id":2}
    //}

    private static function updateSensor(array $update): array
    {
        if (isset($update['encodingType'])) {
            if (is_string($update['encodingType'])) {
                $id = DB::table(TablesName::ENCODING_TYPE)->where('value', '=', $update['encodingType'])->get('id');
                if (count($id) > 0) {
                    $update['encodingType'] = $id[0]->id;
                    //                    return $update;
                } else {
                    throw new Exception('encoding type is not exists', 404);
                }
            } else {
                throw new Exception('invalid encoding type data', 400);
            }
        }
        return $update;
    }

    private static function updateTaskingCap(array $update, $thingId): array
    {
        $updateArr = [];
        if (isset($update['data'])) {
            $updateOBJ = $update['data'];
            foreach ($updateOBJ as $key => $value) {
                if (!is_numeric($key) || !is_numeric($value)) {
                    throw new Exception('cannot update Tasking Capability with this value', 406);
                }
                $taskingCapID = IoTController::getTaskingCapID(["actuator_id" => $key, "thing_id" => $thingId]);
                $data = ["id" => (int)$taskingCapID, "taskingParameters" => (int)$value];
                $updateArr[] = $data;
            }
        } else {
            throw new Exception('Action denied', 403);
        }
        return $updateArr;
    }

    /**
     * @throws Exception
     */
    public static function updateDataStream(int $id, array $update): array
    {
        //cập nhật theo tên đơn vị
        if (key_exists('unitOfMeasurement', $update)) {
            $unitOfMeasurement = $update['unitOfMeasurement'];
            unset($update['unitOfMeasurement']);
            if ($unitOfMeasurement != null) {
                DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)->where('dataStreamId', '=', $id)->delete();
                foreach ($unitOfMeasurement as $item) {
                    //tìm trong DB
                    $queryUnit = DB::table(TablesName::MEASUREMENT_UNIT)->where('id', '=', $item['id'])->get(['id']);
                    if (count($queryUnit) > 0) {
                        $unitId = $queryUnit[0]->id;
                        DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)->insert([
                            'unitId' => $unitId,
                            'dataStreamId' => $id
                        ]);
                    } else {
                        throw new Exception('measurement unit id is not exist', 404);
                    }
                }
            }
        }

        //danh sách thuộc tính đo lường
        if (key_exists('ObservedProperty', $update)) {
            $observedProperty = $update['ObservedProperty'];
            unset($update['ObservedProperty']);
            $queryOp = DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->where('dataStreamId', '=', $id)->get(['id']);

            if ($observedProperty != null) {
                foreach ($observedProperty as $item) {
                    $queryOP = DB::table(TablesName::OBSERVED_PROPERTY)->where('id', '=', $item['id'])->get(['id']);
                    if (count($queryOP) > 0) {
                        $oPId = $queryOP[0]->id;
                        DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->insert([
                            'observedPropertyId' => $oPId,
                            'dataStreamId' => $id
                        ]);
                    } else {
                        throw new Exception('Observed property id is not exist', 404);
                    }
                }
            }
            $arrOpId = [];
            foreach ($queryOp as $opi) {
                array_push($arrOpId, $opi->id);
            }
            DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->delete($arrOpId);
        }

        //các trường trong bảng

        if (isset($update['observationType']) && $update['observationType'] != null) {
            if (is_string($update['observationType'])) {
                $idObservationType = DB::table(ObservationType::TABLE_NAME)->where('value', '=', $update['observationType'])->get('id');
                if (count($idObservationType) > 0) {
                    $update['observationType'] = $idObservationType[0]->id;
                } else {
                    throw new Exception('observation value is not exists', 404);
                }
            } else {
                throw new Exception('invalid observation type');
            }
        }

        return $update;
        //        try {
        //            if(count($update)>0){
        //                static::update(TablesName::MULTI_DATA_STREAM,$id,$update);
        //            }
        //        }catch (Exception $exception){
        //            //lỗi xảy ra khi observationType chưa tồn tại
        //            throw new Exception($exception->getMessage(),$exception->getCode());
        //        }
    }

    //không cần thiết kế phương thức cập nhật sensor sở hữu nhiều datastream như datastream có nhiều measurementunit
    //thing cũng tương tự vậy, không cần thiết cập nhật danh sách data stream của nó

    public static function handleModificationPath(string $path = null): array
    {
        if ($path == null) {
            throw new Exception('path is required', 400);
        }
        $analyze = EntityPathRequest::analyzeVariable($path);

        $pathParams = $analyze['pathParams'];
        $countArray = count($pathParams);
        if ($countArray > 0) {
            //entity cuối có kèm id
            if (isset($pathParams[$countArray - 1]['id']) && $pathParams[$countArray - 1]['id'] != null) {
                $targetEntity = $analyze['last'];
                if (EntityPropertyGetter::isValidEntityPathName($targetEntity)) {
                    //join để kiểm tra hợp lệ
                    // if ($analyze['first']==='taskingcapability'){
                    //     return [
                    //     'entity' => $targetEntity,
                    //     'id' => $pathParams[$countArray - 1]['id']
                    // ];
                    // }
                    $controller = GetController::getEntity($analyze['first']);
                    if ($analyze['first'] !== 'taskingcapability')
                        $controller->paramBuilder($analyze['pathParams']);

                    return [
                        'entity' => $targetEntity,
                        'id' => $pathParams[$countArray - 1]['id']
                    ];
                } else {
                    throw new Exception('invalid entity', 400);
                }
            } else {
                throw new Exception('invalid updating path', 400);
            }
        } else {
            throw new Exception('invalid updating path', 400);
        }
    }
}
