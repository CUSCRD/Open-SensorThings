<?php


namespace App\Http\Controllers\API;

use App\Constant\PathName;

use App\API\EntityGetter\TaskingCapabilities;
use App\API\EntityGetter\Thing;
use App\API\EntityGetter\Actuator;
use App\API\EntityGetter\MultiDataStream;
use App\API\EntityGetter\Task;
use App\API\EntityGetter\User;
use App\API\Helpers\EntityPathRequest;
use App\API\Helpers\EntityQuery;
use App\Constant\TablesName;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class IoTController
{
    /**
     * @throws Exception
     */
    public static function getEntity(string $entityPathName)
    {
        switch ($entityPathName) {
            case Thing::PATH_VARIABLE_NAME:
                $controller = new Thing();
                break;
            case TaskingCapabilities::PATH_VARIABLE_NAME:
                $controller = new TaskingCapabilities();
                break;
            case Actuator::PATH_VARIABLE_NAME:
                $controller = new Actuator();
                break;
            case Task::PATH_VARIABLE_NAME:
                $controller = new Task();
                break;
            case MultiDataStream::PATH_VARIABLE_NAME:
                $controller = new MultiDataStream();
                break;
            default:
                throw new Exception('not supported entity root path', 405);
        }
        return $controller;
    }

    public function action(Request $request, $path = null): JsonResponse
    {
        return
            static::get($request, $path);
    }

    public static function getTaskingCapID($id)
    {
        $taskingCapID = static::findtaskingCapID($id);

        return $taskingCapID ?  $taskingCapID[0]->id : null;
    }


    private static function findTaskingCapID($findID)
    {
        return DB::table(TablesName::TASKINGCAPABILITY)
            ->where('actuator_id', $findID['actuator_id'])
            ->where('thing_id', $findID['thing_id'])
            ->get('id')
            ->toArray();
    }

    public static function getDataStreamID($id)
    {
        $DataStreamID = static::findDataStreamID($id);

        return $DataStreamID ?  $DataStreamID[0]->id : null;
    }


    private static function findDataStreamID($findID)
    {
        $query = DB::table(TablesName::MULTI_DATA_STREAM)
            ->where('sensorId', $findID['sensor_id'])
            ->where('thingId', $findID['thing_id'])
            ->get('id')
            ->toArray();
        return $query;
    }

    private static function isAuto($taskingCapID)
    {
        $taskingCapID = DB::table(TablesName::TASKINGCAPABILITY)
            ->where('id', $taskingCapID)
            ->get('isAuto');
        return $taskingCapID[0]->isAuto === 1;
    }
    private static function get(Request $request, $path = null): JsonResponse
    {
        try {
            $pathVariable = EntityPathRequest::analyzeIoTVariable($path);
            $controller = static::getEntity($pathVariable['first']);
            $builder = $controller->paramBuilder($pathVariable['pathParams']);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }

        $arrayRequest = $request->toArray();
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . '/' . PathName::GET . '/' . $path;
        $analyzeRequestParam = EntityPathRequest::analyzeRequestParam($arrayRequest);
        switch ($pathVariable['first']) {
            case Task::PATH_VARIABLE_NAME:
                $valueResult = "taskingParameters";
                $keyResult = 'status';
                break;
            case MultiDataStream::PATH_VARIABLE_NAME:
                $valueResult = "id";
                $keyResult = 'id';
                break;
            default:
                throw new Exception('not supported entity root path', 405);
        }
        try {
            $ketQua = EntityQuery::getQueryRequestResult($controller, $builder, $pathVariable['last'], $url, $analyzeRequestParam);
            // if ($pathVariable['first'] == 'actuator' && isset($analyzeRequestParam['isAuto']))
            //     return response()->json($ketQua[0]->isAuto);
            return response()->json([$keyResult => (int)$ketQua[0]->$valueResult, 'code' => 200]);
        } catch (Exception $exception) {
            $code = $exception->getCode();
            if (is_numeric($code) && $code >= 100 && $code < 600) {
                return response()->json(['error' => $exception->getMessage()], $exception->getCode());
            } else {
                return response()->json(['error' => $exception->getMessage()], 400);
            }
        }
    }
}
