<?php


namespace App\API\DataManipulation;


use App\Constant\TablesName;
use App\API\EntityGetter\MeasurementUnit;
use App\API\EntityGetter\Observation;
use App\API\Helpers\ApiUtil;
use Exception;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\HeaderBag;

class EntityInsertion
{
    public static function insertThing(array $data): int
    {
        try {
            return DB::table(TablesName::THING)->insertGetId($data);
            // return DB::table(TablesName::THING)->insertGetId([
            //     'name' => $data['name'],
            //     'description' => $data['description'],
            //     'properties' => $data['properties'] == null ? null : json_encode($data['properties'])
            // ]);
        } catch (Exception $exception) {
            throw new Exception('Error while inserting Thing Entity', 400);
        }
    }
    public static function insertTaskingCapabilities(array $data): int
    {
        try {
            return DB::table(TablesName::TASKINGCAPABILITY)->insertGetId($data);
        } catch (Exception $exception) {
            throw new Exception('Error while inserting TaskingCapability Entity', 400);
        }
    }
    public static function insertActuator(array $data): int
    {
        try {
            return DB::table(TablesName::ACTUATOR)->insertGetId($data);
        } catch (Exception $exception) {
            throw new Exception('Error while inserting Actuator Entity', 400);
        }
    }
    public static function insertTask(array $data): int
    {
        try {
            $id = DB::table(TablesName::TASK)
                ->where('id', '=', $data['id'])
                ->exists();
            if ($id) {
                DB::table(TablesName::TASK)
                    ->where('id', $data['id'])
                    ->update(['taskingParameters' => $data['taskingParameters']]);
                return $data['id'];
            }
            return DB::table(TablesName::TASK)->insertGetId($data);
        } catch (Exception $exception) {
            throw new Exception('Error while inserting Task Entity', 400);
        }
    }
    public static function insertObservedProperty(array $data): int
    {
        try {
            return DB::table(TablesName::OBSERVED_PROPERTY)->insertGetId([
                'name' => $data['name'],
                'definition' => $data['definition'],
                'description' => $data['description'] == null ? null : $data['description'],
            ]);
        } catch (Exception $exception) {
            throw new Exception('Error while insert Observed Property entity', 400);
        }
    }
    public static function insertEncodingType(array $data): int
    {
        try {
            $id = DB::table(TablesName::ENCODING_TYPE)->insertGetId([
                'name' => $data['name'],
                'value' => $data['value']
            ]);
            return $id;
        } catch (Exception $exception) {
            throw new Exception('Error while inserting Encoding Type entity', 400);
        }
    }

    /**
     * @throws Exception
     */
    public static function insertSensor(array $dataSensor, array $datastreamArray = null): int
    {
        //        string $name, string $description, $encodingType, string $metadata=null

        $resultType = DB::table(TablesName::ENCODING_TYPE)->where('value', '=', $dataSensor['encodingType'])->get(['id']);
        if (count($resultType) > 0) {
            $typeId = $resultType[0]->id;
        } else {
            throw new Exception('encoding type is not exist', 400);
            //                $typeId=static::insertNewEncodingType('newType',$encodingType);
        }
        try {
            $idSensor = DB::table(TablesName::SENSOR)->insertGetId([
                'name' => $dataSensor['name'],
                'description' => $dataSensor['description'],
                'encodingType' => $typeId,
                'metadata' => $dataSensor['metadata'] ?? null,
            ]);
        } catch (Exception $exception) {
            throw new Exception('Error while inserting Sensor entity', 400);
        }

        if ($datastreamArray != null) {
            foreach ($datastreamArray as $datastreamItem) {
                //tạo mới datastreams
                //ds và s được liên kết tại hàm này
                $sensorDS = ['id' => $idSensor];
                $inputs = [
                    'sensor' => $sensorDS,
                    'thing' => $datastreamItem['Thing'],
                    'name' => $datastreamItem['name'],
                    'description' => $datastreamItem['description'],
                    'observationType' => $datastreamItem['observationType'] ?? 'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_ComplexObservation',
                    'unitOfMeasurement' => $datastreamItem['unitOfMeasurement'] ?? null,
                    'observations' => $datastreamItem['Observations'] ?? null,
                    'observedProperty' => $datastreamItem['ObservedProperty'] ?? null,
                    'multiObservationDataType' => $datastreamItem['multiObservationDataType'] ?? null
                ];
                static::insertDataStream($inputs);
            }
        }
        return $idSensor;
    }
    public static function insertMeasurementUnit(array $data): int
    {
        try {
            return DB::table(TablesName::MEASUREMENT_UNIT)->insertGetId([
                'name' => $data['name'],
                'definition' => $data['definition'],
                'symbol' => $data['symbol']
            ]);
        } catch (Exception $exception) {
            throw new Exception('invalid data: measurement unit', 400);
        }
    }
    /**
     * @throws Exception
     */
    //create and link to available
    public static function insertObservation(array $data): int
    {
        $check = DB::table(TablesName::MULTI_DATA_STREAM)->where('id', '=', $data['dataStreamId'])->get();
        if (count($check) > 0) {
            try {
                return DB::table(TablesName::OBSERVATION)->insertGetId([
                    'dataStreamId' => $data['dataStreamId'],
                    'result' => json_encode($data['result']),
                    'resultTime' => $data['resultTime'] ?? OgcUtil::now(),
                    'validTime' => $data['validTime'] ?? null
                ]);
            } catch (Exception $exception) {
                throw new Exception('error while inserting observation', 400);
            }
        } else {
            throw new Exception('data stream id not exist', 404);
        }
    }

    public static function createObservationExtension(array $request): array
    {
        $result = [];
        foreach ($request as $itemDS) {
            //bên trong item có: datastream, component, dataArray
            $dataStreamId = $itemDS['Datastream']['id'] ?? null;
            if ($dataStreamId == null) {
                throw new Exception('invalid data stream data', 400);
            }
            $components = $itemDS['components'] ?? null;
            if ($components == null) {
                throw new Exception('invalid component data', 400);
            } elseif (!in_array('result', $components)) {
                throw new Exception('result must present', 400);
            }
            $dataArray = $itemDS['dataArray'] ?? null;
            if ($dataArray != null) {
                //xử lý từng item observation
                foreach ($dataArray as $itemObservation) {
                    //tạo mảng dữ liệu
                    $observation = [];
                    //lấy tên thuộc tính, gắn giá trị vào
                    foreach ($components as $index => $value) {
                        if ($value == 'resultTime') {
                            //client không có đồng hồ
                            if ($itemObservation[$index] == null) {
                                //tạo datetime string
                                $dataTime = OgcUtil::now();
                            } else {
                                $dataTime = $itemObservation[$index];
                            }
                            $observation[$value] = $dataTime;
                        } else {
                            $observation[$value] = $itemObservation[$index];
                        }
                    }
                    $observation['dataStreamId'] = $dataStreamId;

                    try {
                        $idObservation = EntityInsertion::insertObservation($observation);
                        array_push($result, EntityCreation::createLocation(Observation::PATH_VARIABLE_NAME, $idObservation));
                    } catch (Exception $exception) {
                        //                        array_push($result,$exception->getMessage());
                        array_push($result, 'error');
                    }
                }
            }
        }
        return $result;
    }
    public static function insertObservationType(array $inputs): int
    {
        $code = $inputs['code'];
        $value = $inputs['value'];
        $result = $inputs['result'];

        try {
            return DB::table(TablesName::OBSERVATION_TYPE)->insertGetId([
                'code' => $code,
                'value' => $value,
                'result' => $result
            ]);
        } catch (Exception $exception) {
            throw new Exception('error while inserting Observation Type', 400);
        }
    }
    /**
     * @throws Exception
     */
    /*
    sample data:
{
    "name": "oven temperature",
    "description": "This is a datastream for an oven’s internal temperature.",
    "unitOfMeasurement": [
        {
            "name": "degree Celsius",
            "symbol": "°C",
            "definition": "http://unitsofmeasure.org/ucum.html#para-30"
        }
    ],
    "observationType": "http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_Measurement",
    "Observations": [
        {
            "resultTime": "2012-06-26",
            "result": 70.4
        }
    ],
    "ObservedProperty": [
        {
            "name": "DewPoint Temperature",
            "definition": "http://sweet.jpl.nasa.gov/ontology/property.owl#DewPointTemperature",
            "description": "The dewpoint temperature is the temperature to which the air must be cooled, at constant pressure, for dew to form. As the grass and other objects near the ground cool to the dewpoint, some of the water vapor in the atmosphere condenses into liquid water on the objects."
        }
    ],
    "Sensor": {
        "name": "DS18B20",
        "description": "DS18B20 is an air temperature sensor…",
        "encodingType": "application/pdf",
        "metadata": "http://datasheets.maxim-ic.com/en/ds/DS18B20.pdf"
    },
    "Thing":{
    	"id":1
    }
}
    */
    public static function insertDataStream(array $inputs, HeaderBag $header = null): int
    {
        $sensor = $inputs['sensorId'];
        $thing = $inputs['thingId'];
        $name = $inputs['name'];
        $description = $inputs['description'];
        $observationType = $inputs['observationType'];
        // $unitOfMeasurement = $inputs['unitOfMeasurement'];
        // $observations = $inputs['observations'];
        // $observedProperties = $inputs['observedProperty'];
        // $multiObservationDataType = $inputs['multiObservationDataType'];
        //lấy id của sensor để datastream liên kết tới sensor

        //tạo datastream cùng lúc tạo sensor
        if (isset($sensor) && $sensor != null) {
            //kiểm tra trong DB
            $ss = DB::table(TablesName::SENSOR)->where('id', '=', $sensor)->get('id');
            if (count($ss) > 0) {
                $ssId = $ss[0]->id;
            } else {
                throw new Exception('invalid Sensor', 400);
            }
        } else {
            throw new Exception('Sensor is required', 400);
        }

        //get thing
        if (isset($thing) && $thing != null) {
            $t = DB::table(TablesName::THING)->where('id', '=', $thing)->get('id');
            if (count($t) > 0) {
                $tId = $t[0]->id;
            } else {
                throw new Exception('invalid Thing', 400);
            }
        } else {
            throw new Exception('Thing is required', 400);
        }
        $checkObservationType = DB::table(TablesName::OBSERVATION_TYPE)->where('value', '=', $observationType)->get(['id']);
        //nếu loại giá trị đo đạc đã có sẵn: lấy id
        if (count($checkObservationType) > 0) {
            $observationTypeId = $checkObservationType[0]->id;
        } else {
            throw new Exception("invalid observation type", 404);
        }

        //thêm datastream
        try {
            $idDataStream = DB::table(TablesName::MULTI_DATA_STREAM)->insertGetId([
                'sensorId' => $ssId,
                'thingId' => $tId,
                'name' => $name,
                'description' => $description,
                'observationType' => $observationTypeId,
            ]);
        } catch (Exception $exception) {
            throw new Exception('error while inserting Data Stream', 400);
        }

        //đã có data stream
        //sử dụng id của nó:
        return $idDataStream;
        //xử lý đơn vị
        $idArrayUnits = []; //mảng chứa id các đơn vị mà datastream này đo đạc
        //đối chiếu và thêm mới
        if ($unitOfMeasurement != null) {
            foreach ($unitOfMeasurement as $unit) {
                if (isset($unit['name']) && $unit['name'] != null) {
                    $resultUnit = DB::table(TablesName::MEASUREMENT_UNIT)->where('name', '=', $unit['name'])->get(['id']);
                    if (count($resultUnit) > 0) {
                        //đã tồn tại trong DB
                        array_push($idArrayUnits, $resultUnit[0]->id);
                    } else {
                        //thêm mới
                        if (isset($unit['symbol'])) {
                            if (isset($unit['definition'])) {
                                $idUnit = static::insertMeasurementUnit($unit);
                                array_push($idArrayUnits, $idUnit);
                            } else {
                                throw new Exception(MeasurementUnit::PATH_VARIABLE_NAME . ': definition not found', 400);
                            }
                        } else {
                            throw new Exception('symbol not found', 400);
                        }
                    }
                } else {
                    if (isset($unit['id']) && $unit['id'] != null && is_integer($unit['id'])) {
                        array_push($idArrayUnits, $unit['id']);
                    }
                }
            }
        }

        $idArrayUnits = array_unique($idArrayUnits);
        //liên kết với data stream
        foreach ($idArrayUnits as $unit) {
            try {
                DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)->insert([
                    'unitId' => $unit,
                    'dataStreamId' => $idDataStream
                ]);
            } catch (Exception $exception) {
                throw new Exception('error while linking Data Stream and Measurement Unit', 400);
            }
        }



        //xử lý observation nếu có kèm theo
        //observation này không cần mang theo id của datastream
        //nếu nó mang theo id, id của nó sẽ bị bỏ qua
        if ($observations != null) {
            if (count($observations) > 0) {
                foreach ($observations as $observation) {
                    if (isset($observation['result']) && $observation['result'] != null) {
                        $dataObservation = [
                            'dataStreamId' => $idDataStream,
                            'result' => $observation['result'],
                            'resultTime' => $observation['resultTime'] ?? null,
                            'validTime' => $observation['validTime'] ?? null
                        ];
                        static::insertObservation($dataObservation);
                    } else {
                        throw new Exception('result attribute not found in observation object', 400);
                    }
                }
            }
        }

        //xử lý thuộc tính đo lường. nếu thuộc tính rỗng hoặc null là một dạng lỗi, nhưng tạm thời chấp nhận xử lý
        if ($observedProperties != null) {
            if (count($observedProperties) > 0) {
                $idOPArray = [];
                foreach ($observedProperties as $observedProperty) {
                    if (isset($observedProperty['id'])) {
                        array_push($idOPArray, $observedProperty['id']);
                    } else if (isset($observedProperty['name'])) {
                        //kiểm tra tồn tại
                        //trong tài liệu OGC có ví dụ liên kết với một ObservedProperty entity có sẵn,
                        //nhưng lại là json định nghĩa nó, việc này sẽ gây lãng phí bới vì thực tế chỉ cần tên hoặc id là được

                        $resultIDOP = DB::table(TablesName::OBSERVED_PROPERTY)->where('name', '=', $observedProperty['name'])->get('id');
                        if (count($resultIDOP) > 0) {
                            array_push($idOPArray, $resultIDOP[0]->id);
                        } else {
                            //thêm mới
                            if (isset($observedProperty['definition'])) {
                                if (isset($observedProperty['description'])) {
                                    $idOP = static::insertObservedProperty($observedProperty);
                                    array_push($idOPArray, $idOP);
                                } else {
                                    throw new Exception('invalid observed property object: description attribute not found', 400);
                                }
                            } else {
                                throw new Exception('invalid observed property object: definition attribute not found', 400);
                            }
                        }
                    } else {
                        throw new Exception('invalid observed property object: name attribute not found', 400);
                    }
                }
                $idOPArray = array_unique($idOPArray);
                //insert observed properties for datastream
                foreach ($idOPArray as $idOpItem) {
                    try {
                        DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->insert([
                            'dataStreamId' => $idDataStream,
                            'observedPropertyId' => $idOpItem
                        ]);
                    } catch (Exception $exception) {
                        throw new Exception('error while linking Data Stream and Observed Property', 400);
                    }
                }
            }
        }

        //multi observation data type
        //thuộc tính này phải có, giả định như nó có thể lỗi
        if ($multiObservationDataType != null) {
            if (count($multiObservationDataType) > 0) {
                foreach ($multiObservationDataType as $itemODT) {
                    if (isset($itemODT['value'])) {
                        $modtCheck = DB::table(TablesName::OBSERVATION_TYPE)->where('value', '=', $itemODT['value'])->get(['id']);
                        if (count($modtCheck) > 0) {
                            $observationTypeId = $modtCheck[0]->id;
                        } else {
                            $inputOT = [
                                'code' => 'No_Code',
                                'value' => $itemODT,
                                'result' => 'unknown'
                            ];
                            $observationTypeId = static::insertObservationType($inputOT);
                        }
                        try {
                            DB::table(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE)->insert([
                                'dataStreamId' => $idDataStream,
                                'observedType' => $observationTypeId
                            ]);
                        } catch (Exception $exception) {
                            throw new Exception('error while linking Observed Type and Data Stream', 400);
                        }
                    } else {
                        throw new Exception('$multiObservationDataType: property "value" is required', 400);
                    }
                }
            }
        }
        return $idDataStream;
    }

    /**
     * Phục vụ cho Batch Request
     *   Những Entity cần reference id từ header là các Entity phụ thuộc vào entity khác đã có sẵn từ trước,
     * không thể đững một mình.
     *
     * Các Entity đó hiện tại bao gồm MultiDataStream và Observation
     *
     * Đối số:
     *
     * header chứa ID cần thiết để reference
     *
     * id là key cần thiết để lấy giá trị từ header
     *
     * chẳng hạn như id item trong change set của 1 sensor là "sensor1",
     * thì datastream được thêm trong cùng change set muốn liên kết tới nó,
     * datastream sẽ lấy key "sensor1" là id của sensor được thêm trong cùng change set,
     * khi sensor được thêm vào CSDL thì id của nó sẽ đưa vào header để các entry sau có thể dùng đến
     * */
    public static function getIdHeader(string $id, HeaderBag $header = null)
    {
        //nếu có reference tới entity khác trong cùng change set
        //có truyền header
        if ($header != null) { //có tồn tại id để reference
            //cắt chuỗi, bỏ dấu $
            $headerId = $header->get(substr($id, 1));
            if ($headerId != null) {
                return $headerId;
            } else {
                throw new Exception('referenced id is not exist', 400);
            }
        } else {
            throw new Exception('header is null', 400);
        }
    }
}
