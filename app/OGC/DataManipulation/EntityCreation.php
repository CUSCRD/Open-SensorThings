<?php


namespace App\OGC\DataManipulation;


use App\Constant\PathName;
use App\OGC\EntityGetter\MeasurementUnit;
use App\OGC\EntityGetter\MultiDataStream;
use App\OGC\EntityGetter\Observation;
use App\OGC\EntityGetter\ObservationType;
use App\OGC\EntityGetter\ObservedProperty;
use App\OGC\EntityGetter\Sensor;
use App\OGC\EntityGetter\Thing;
use App\OGC\Helpers\EntityPathRequest;
use App\OGC\Helpers\OgcUtil;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\HeaderBag;

class EntityCreation
{
    /**
     * Header
     *
     * @var HeaderBag
     */
    protected $header;

    /**
     * json dữ liệu nhận được
     *
     * @var array
     */
    protected $request;

    /**
     * đường dẫn để thêm dữ liệu cho entity này
     *
     * @var string
     */
    protected $path;


    /**
     * chứa tên và id entity xuất hiện trên path gần entity được thêm nhất
     *
     * id và entity được tạo trước đó được chỉ định cho lần thêm sub-collection này
     *
     * hiện tại chỉ có 2 entity dùng thuộc tính này đó là Observation và MultiDataStream
     *
     * Observation thì có thể lấy id của MultiDataStream
     *
     * DataStream có thể lấy id của Sensor và Thing
     *
     * @var array
     */
    protected $parent;

    public function __construct(array $request, string $path = null, HeaderBag $header = null)
    {
        $this->request = $request;
        $this->path = $path;
        $this->header = $header;
    }

    /**
     * @throws Exception
     */
    public function create(): array
    {
        try {
            $handle = EntityPathRequest::handleCreationPath($this->path);
        } catch (Exception $exception) {
            throw new Exception('handle path error: ' . $exception->getMessage(), 400);
        }
        $targetEntity = $handle['target'];
        $this->parent = $handle['parent'];
        $request = $this->request;


        DB::beginTransaction();

        try {
            if ($targetEntity == PathName::CREATE_OBSERVATION) {
                //không có trung gian
                if (count($handle['parent']) == 0) {
                    $result = static::createObservationExtension($request);
                    $result = ['data' => $result, 'code' => 200];
                } else {
                    throw new Exception('invalid create observation path', 400);
                }
            } else {
                switch ($targetEntity) {
                        //encoding type không cần thay đổi nhiều
                        //nếu muốn thay đổi thì thực hiện query lúc bảo trì project này
                        //encoding type KHÔNG NÊN có trong danh sách thay đổi
                    case 'encodingtypes':
                        $id = $this->createEncodingType($request);
                        break;
                    case MeasurementUnit::PATH_VARIABLE_NAME:
                        $id = $this->createMeasurementUnit($request);
                        break;
                    case MultiDataStream::PATH_VARIABLE_NAME:
                        $id = $this->createDataStream($request);
                        break;
                    case Observation::PATH_VARIABLE_NAME:
                        $id = $this->createObservation($request);
                        break;
                    case ObservationType::PATH_VARIABLE_NAME:
                        $id = $this->createObservationType($request);
                        break;
                    case ObservedProperty::PATH_VARIABLE_NAME:
                        $id = $this->createObservedProperty($request);
                        break;
                    case Sensor::PATH_VARIABLE_NAME:
                        $id = $this->createSensor($request);
                        break;
                    case Thing::PATH_VARIABLE_NAME:
                        $id = $this->createThing($request);
                        break;
                    default:
                        throw new Exception('path param ' . $targetEntity . ' is not supported', 405);
                }

                //            $data=OgcUtil::getEntityById($targetEntity,$id);
                $data = ['message' => 'success'];
                $code = 201;
                $location = static::createLocation($targetEntity, $id);

                $result = [
                    'data' => $data,
                    'code' => $code,
                    'Location' => $location
                ];
            }
            DB::commit();
            return $result;
        } catch (Exception $exception) {
            DB::rollBack();
            throw new Exception($exception->getMessage(), $exception->getCode());
        }
    }

    protected function createMeasurementUnit(array $request): int
    {
        $name = $request['name'] ?? null;
        if ($name == null) {
            throw new Exception('invalid measurement unit data: invalid name property', 400);
        }
        $symbol = $request['symbol'] ?? null;
        $definition = $request['definition'] ?? null;
        $data = [
            'name' => $name,
            'definition' => $definition,
            'symbol' => $symbol
        ];
        return EntityInsertion::insertMeasurementUnit($data);
    }
    protected function createEncodingType(array $request): int
    {
        $name = $request['name'] ?? null;
        $value = $request['value'] ?? null;
        if ($name == null || $value == null) {
            throw new Exception('invalid encoding type data: name is invalid', 400);
        }
        $data = [
            'name' => $name,
            'value' => $value
        ];
        return EntityInsertion::insertEncodingType($data);
    }
    protected function createObservedProperty(array $request): int
    {
        $name = $request['name'] ?? null;
        $definition = $request['definition'] ?? null;
        $description = $request['description'] ?? null;

        if ($name == null || $definition == null || $description == null) {
            throw new Exception('invalid observed property data', 400);
        }
        $data = [
            'name' => $name,
            'definition' => $definition,
            'description' => $description,
        ];
        return EntityInsertion::insertObservedProperty($data);
    }
    protected function createSensor(array $request): int
    {
        $name = $request['name'] ?? null;
        $description = $request['description'] ?? null;
        $encodingType = $request['encodingType'] ?? null;
        $metadata = $request['metadata'] ?? null;
        if ($name == null || $encodingType == null) {
            throw new Exception('invalid sensor property data: name or description or encoding type', 400);
        }

        //encoding type là value, không phải id

        $data = [
            'name' => $name,
            'description' => $description,
            'encodingType' => $encodingType,
            'metadata' => $metadata,
        ];
        $id = EntityInsertion::insertSensor($data);
        $data['id'] = $id;
        if (isset($request['DataStreams']) && $request['DataStreams'] != null) {
            $dsRepresentation = $request['DataStreams'];
            //nó đang ở dạng mảng datastream
            //thêm id của sensor cho nó
            //rồi insert từng cái một
            foreach ($dsRepresentation as $item) {
                $item['Sensor'] = ['id' => $id];
                $this->createDataStream($item);
            }
        }
        return $id;
    }
    protected function createThing(array $request): int
    {
        $name = $request['name'] ?? null;
        $description = $request['description'] ?? null;
        $properties = $request['properties'] ?? null;

        if ($name == null || $description == null) {
            throw new Exception('invalid Thing property data: both name and description are required', 400);
        }
        $data = [
            'name' => $name,
            'description' => $description,
            'properties' => $properties
        ];
        $id = EntityInsertion::insertThing($data);
        $data['id'] = $id;

        if (isset($request['DataStreams']) && $request['DataStreams'] != null) {
            $dsRepresentation = $request['DataStreams'];
            //nó đang ở dạng mảng datastream
            //thêm id của thing cho nó
            foreach ($dsRepresentation as $item) {
                $item['Thing'] = ['id' => $id];
                $this->createDataStream($item);
            }
        }
        return $id;
    }
    protected function createObservationType(array $request): int
    {
        $code = $request['code'] ?? Null;
        $value = $request['value'] ?? null;
        $result = $request['result'] ?? null;
        if ($code == null || $value == null || $result == null) {
            throw new Exception('invalid Observation type property data: code, value, result must be valid', 400);
        } else {
            $data = ['code' => $code, 'value' => $value, 'result' => $result];
            return EntityInsertion::insertObservationType($data);
        }
    }

    //khi tạo obs, KHÔNG được phép kèm theo 1 datastream mới
    protected function createObservation(array $request): int
    {
        $result = $request['result'] ?? null;
        $resultTime = $request['resultTime'] ?? null;
        $validTime = $request['validTime'] ?? null;
        if (isset($request['Datastream'])) {
            $dataStream = $request['Datastream'];
        } else {
            if ($this->parent['name'] == MultiDataStream::PATH_VARIABLE_NAME) {
                $dataStream = ['id' => $this->parent['id']];
            } else {
                throw new Exception('Data Stream id of Observation is not exist', 404);
            }
        }

        if (isset($dataStream['id']) && $dataStream['id'] != null) {
            $idDataStream = $dataStream['id'];

            if (is_string($idDataStream) && $idDataStream[0] == '$') {
                $idDataStream = EntityInsertion::getIdHeader($idDataStream, $this->header);
            }

            $data = [
                'dataStreamId' => $idDataStream,
                'result' => $result,
                'validTime' => $validTime,
                'resultTime' => $resultTime,
            ];
            return EntityInsertion::insertObservation($data);
        }
        throw new Exception('data stream id of Observation not found', 404);
    }

    protected function createDataStream(array $request): int
    {
        $parent = $this->parent;
        if (isset($request['Sensor'])) {
            $sensor = $request['Sensor'];
        } else {
            if ($parent['name'] == Sensor::PATH_VARIABLE_NAME) {
                $sensor = ['id' => $parent['id']];
            } else {
                throw new Exception('Sensor id of Data Stream is not exist', 404);
            }
        }

        if (isset($request['Thing'])) {
            $thing = $request['Thing'];
        } else {
            if ($parent['name'] == Thing::PATH_VARIABLE_NAME) {
                $thing = ['id' => $parent['id']];
            } else {
                throw new Exception('Thing id of Data Stream is not exist', 404);
            }
        }
        $inputs = [
            'sensor' => $sensor,
            'thing' => $thing,
            'name' => $request['name'],
            'description' => $request['description'],
            'observationType' => $request['observationType'] ?? 'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_ComplexObservation',
            'unitOfMeasurement' => $request['unitOfMeasurement'] ?? null,
            'observations' => $request['Observations'] ?? null,
            'observedProperty' => $request['ObservedProperty'] ?? null,
            'multiObservationDataType' => $request['multiObservationDataType'] ?? null
        ];

        $id = EntityInsertion::insertDataStream($inputs, $this->header);
        return $id;
    }

    protected static function createObservationExtension(array $request)
    {
        return EntityInsertion::createObservationExtension($request);
    }

    public static function createLocation(string $path, string $id): string
    {
        return PathName::GET . '/' . $path . '(' . $id . ')';
    }
}
