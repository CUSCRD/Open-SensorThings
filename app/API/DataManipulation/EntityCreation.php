<?php


namespace App\API\DataManipulation;

use App\API\EntityGetter\Actuator;
use App\Constant\PathName;
use App\API\EntityGetter\MeasurementUnit;
use App\API\EntityGetter\MultiDataStream;
use App\API\EntityGetter\Observation;
use App\API\EntityGetter\ObservationType;
use App\API\EntityGetter\ObservedProperty;
use App\API\EntityGetter\Sensor;
use App\API\EntityGetter\TaskingCapabilities;
use App\API\EntityGetter\Task;
use App\API\EntityGetter\Thing;
use App\API\EntityGetter\Location;
use App\API\Helpers\EntityPathRequest;
use App\API\Helpers\ApiUtil;
use App\Constant\TablesName;
use App\Models\User\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
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
                    case TaskingCapabilities::PATH_VARIABLE_NAME:
                        $id = $this->createTaskingCap($request);
                        break;
                    case Actuator::PATH_VARIABLE_NAME:
                        $id = $this->createActuator($request);
                        break;
                    case Task::PATH_VARIABLE_NAME:
                        $id = $this->createTask($request);
                        break;
                    case Location::PATH_VARIABLE_NAME:
                        $id = $this->createLocationEntity($request);
                        break;
                    default:
                        throw new Exception('path param ' . $targetEntity . ' is not supported', 405);
                }

                //            $data=OgcUtil::getEntityById($targetEntity,$id);
                $code = 201;
                $location = static::createLocation($targetEntity, $id);
                $data = [
                    'message' => 'success',
                    // 'location' => $location
                ];

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
        $properties =  isset($request['properties']) ? ($request['properties'] == null ? null : json_encode($request['properties'])) : null;
        $avt_image = $request['avt_image'] ?? null;

        $user = DB::table(TablesName::Users)
            ->where('remember_token', $this->header->get('token'))
            ->get(['id'])
            ->first();
        if ($user == null)
            throw  new Exception('User is invalid', 400);
        $id_user = $user->id;

        $location = $request['Location'] ?? null;
        if ($location == null)
            throw new Exception('Location are required', 400);

        $locationId = null;
        if (isset($location['id'])) {
            $locationId = $location['id'];
            $isLocationExisting = DB::table(TablesName::LOCATION)
                ->where('id', '=', $locationId)
                ->exists();
            if (!$isLocationExisting)
                throw new Exception('Location is not found', 404);
        } else {
            $newLocationId = $this->createLocationEntity($location);
            $locationId = $newLocationId;
        }

        if ($name == null || $description == null) {
            throw new Exception('invalid Thing property data: both name and description are required', 400);
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'properties' => $properties,
            'id_user' => $id_user,
            'id_location' => $locationId,
        ];

        $id = EntityInsertion::insertThing($data);
        $data['id'] = $id;
        static::updateImage('avt_image', $avt_image, $id, 'Thing');

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
    protected function updateImage($field, $request, $id, $tableName)
    {
        if ($request === null) {
            return;
        }
        $validationArray = [
            'avt_image' => [
                'required',
                'image',
                'mimes:jpeg,png',
                'mimetypes:image/jpeg,image/png',
                'max:2048',
            ]
        ];
        $customMessages = [];
        $validator = Validator::make(["avt_image" => $request], $validationArray, $customMessages);
        if ($validator->fails()) {
            return response()->json([
                $validator->messages()->all(),
            ])->setStatusCode(400);
        }
        $path = $request->store('images', 's3');
        Storage::disk('s3')->setVisibility($path, 'public');
        DB::table($tableName)
            ->where('id', $id)
            ->update([$field => Storage::disk('s3')->url($path)]);
    }
    protected function createTaskingCap(array $request): int
    {
        $name = $request['name'] ?? null;
        $description = $request['description'] ?? null;
        $taskingParameters =  !isset($request['taskingParameters']) ? null : json_encode($request['taskingParameters']);
        $actuator = $request['Actuator'] ?? null;
        $thing = $request['Thing'] ?? null;
        if ($name == null || $description == null) {
            throw new Exception('invalid TaskingCapabilities property data: both name and description are required', 400);
        }

        if ($actuator == null || $thing == null)
            throw new Exception('Actuator and Thing are required', 400);

        $actuatorId = null;
        if (isset($actuator['id'])) {
            $actuatorId = $actuator['id'];
            $isActuatorExisting = DB::table(TablesName::ACTUATOR)
                ->where('id', '=', $actuatorId)
                ->exists();
            if (!$isActuatorExisting)
                throw new Exception('Actuator is not found', 404);
        } else {
            $newActuatorId = $this->createActuator($actuator);
            $actuatorId = $newActuatorId;
        }

        $thingId = null;
        if (isset($thing['id'])) {
            $thingId = $thing['id'];
            $isthingExisting = DB::table(TablesName::THING)
                ->where('id', '=', $thingId)
                ->exists();
            if (!$isthingExisting)
                throw new Exception('Thing is not found', 404);
        } else {
            throw new Exception('id of Thing is required', 400);
        }

        $data = [
            'name' => $name,
            'description' => $description,
            'taskingParameters' => $taskingParameters,
            'actuator_id' => $actuatorId,
            'thing_id' => $thingId
        ];
        $id = EntityInsertion::insertTaskingCapabilities($data);
        // $data['id'] = $id;
        // if (isset($request['DataStreams']) && $request['DataStreams'] != null) {
        //     $dsRepresentation = $request['DataStreams'];
        //     //nó đang ở dạng mảng datastream
        //     //thêm id của thing cho nó
        //     foreach ($dsRepresentation as $item) {
        //         $item['Thing'] = ['id' => $id];
        //         $this->createDataStream($item);
        //     }
        // }
        return $id;
    }
    protected function createTask(array $request): int
    {
        if (!isset($request['TaskingCapability']['id']))
            throw new Exception("TaskingCapability is invalid, id of TaskingCapability is required", 400);

        $taskingCapabilityId = $request['TaskingCapability']['id'];

        $isTaskingCapabilityExisting = DB::table(TablesName::TASKINGCAPABILITY)
            ->where('id', '=', $taskingCapabilityId)
            ->exists();

        if (!$isTaskingCapabilityExisting)
            throw new Exception("TaskingCapability is not found", 404);

        $taskingParameters = !isset($request['taskingParameters']) ? null : json_encode($request['taskingParameters']);
        $status = $request['xStatus'] ?? 'new';

        $data = [
            'taskingParameters' => $taskingParameters,
            'xStatus' => $status,
            'taskingCapabilityId' => $taskingCapabilityId,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        $id = EntityInsertion::insertTask($data);

        return $id;
    }
    protected function createActuator(array $request): int
    {
        $name = $request['name'] ?? null;
        $description = $request['description'] ?? null;
        $encodingType = $request['encodingType'] ?? null;
        if ($name == null || $description == null) {
            throw new Exception('invalid Actuator property data: both name and description are required', 400);
        }
        $data = [
            'name' => $name,
            'description' => $description,
            'encodingType' => $encodingType,
        ];
        $id = EntityInsertion::insertActuator($data);
        return $id;
    }
    protected function createLocationEntity(array $request): int
    {

        $name = $request['name'] ?? null;
        $description = $request['description'] ?? null;
        $coordinates = $request['coordinates'] ?? null;
        if ($name == null || $description == null || $coordinates == null) {
            throw new Exception('invalid Location property data: coordinates, name and description are required', 400);
        }
        $data = [
            'name' => $name,
            'description' => $description,
            'coordinates' => $coordinates,
        ];
        $id = EntityInsertion::insertLocation($data);
        $data['id'] = $id;
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
        $sensorId = null;
        if (isset($request['Sensor'])) {
            $sensor = $request['Sensor'];
            if (isset($sensor['id'])) {
                $isSensorExisting = DB::table(TablesName::SENSOR)
                    ->where("id", "=", $sensor['id'])
                    ->exists();
                if (!$isSensorExisting)
                    throw new Exception("Sensor is not found", 404);
                $sensorId = $sensor['id'];
            } else {
                $newSensorId = $this->createSensor($sensor);
                $sensorId = $newSensorId;
            }
        }

        $thingId = null;
        if (isset($request['Thing'])) {
            $thing = $request['Thing'];
            if (isset($thing['id'])) {
                $isThingExisting = DB::table(TablesName::THING)
                    ->where("id", "=", $thing['id'])
                    ->exists();
                if (!$isThingExisting)
                    throw new Exception("Thing is not found", 404);
                $thingId = $thing['id'];
            } else {
                $newThingId = $this->createThing($thing);
                $thingId = $newThingId;
            }
        }

        $inputs = [
            'sensorId' => $sensorId,
            'thingId' => $thingId,
            'name' => $request['name'],
            'description' => $request['description'],
            'observationType' => $request['observationType'] ?? 'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_ComplexObservation',
            'unitOfMeasurement' => $request['UnitOfMeasurements'] ?? null,
            // 'observations' => $request['Observations'] ?? null,
            // 'observedProperty' => $request['ObservedProperty'] ?? null,
            // 'multiObservationDataType' => $request['multiObservationDataType'] ?? null
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
