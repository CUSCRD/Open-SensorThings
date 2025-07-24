<?php


namespace App\Http\Controllers\API;

use App\Constant\PathName;
use App\API\EntityGetter\MeasurementUnit;
use App\API\EntityGetter\MultiDataStream;
use App\API\EntityGetter\Observation;
use App\API\EntityGetter\ObservationType;
use App\API\EntityGetter\ObservedProperty;
use App\API\EntityGetter\Sensor;
use App\API\EntityGetter\TaskingCapabilities;
use App\API\EntityGetter\Thing;
use App\API\EntityGetter\Actuator;
use App\API\EntityGetter\Location;
use App\API\EntityGetter\Task;
use App\API\EntityGetter\User;
use App\API\Helpers\EntityPathRequest;
use App\API\Helpers\EntityQuery;
use App\Constant\TablesName;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GetController
{
    /**
     * @throws Exception
     */
    public static function getEntity(string $entityPathName)
    {
        switch ($entityPathName) {
            case MeasurementUnit::PATH_VARIABLE_NAME:
                $controller = new MeasurementUnit();
                break;
            case MultiDataStream::PATH_VARIABLE_NAME:
                $controller = new MultiDataStream();
                break;
            case Observation::PATH_VARIABLE_NAME;
                $controller = new Observation();
                break;
            case ObservationType::PATH_VARIABLE_NAME:
                $controller = new ObservationType();
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $controller = new ObservedProperty();
                break;
            case Sensor::PATH_VARIABLE_NAME:
                $controller = new Sensor();
                break;
            case Thing::PATH_VARIABLE_NAME:
                $controller = new Thing();
                break;
            case Location::PATH_VARIABLE_NAME:
                $controller = new Location();
                break;
            case TaskingCapabilities::PATH_VARIABLE_NAME: // ver 2.0
                $controller = new TaskingCapabilities();
                break;
            case Actuator::PATH_VARIABLE_NAME:
                $controller = new Actuator();
                break;
            case Task::PATH_VARIABLE_NAME:
                $controller = new Task();
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

    private static function getStatusActuator(array $requests, int $thing_id)
    {
        foreach ($requests as $key => $request) {
            $actuator_id = $request->id;
            $query = DB::table(TablesName::TASKINGCAPABILITY)
                ->where([
                    'actuator_id' => $actuator_id,
                    'thing_id' => $thing_id
                ])
                ->join('task', 'task.id', 'tasking_capability.id')
                ->get('task.taskingParameters')
                ->toArray();
            $controlState = 0;
            if ($query)
                $controlState = $query[0]->taskingParameters;
            $request->controlState = $controlState;
            $requests[$key] = $request;
        }
        return $requests;
    }

    private static function get(Request $request, $path = null): JsonResponse
    {
        try {
            $pathVariable = EntityPathRequest::analyzeVariable($path);
            $controller = static::getEntity($pathVariable['first']);
            $builder = $controller->paramBuilder($pathVariable['pathParams']);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
        $arrayRequest = $request->toArray();
        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . '/api' . '/' . PathName::GET . '/' . $path;
        $analyzeRequestParam = EntityPathRequest::analyzeRequestParam($arrayRequest);
        try {
            $ketQua = EntityQuery::getQueryRequestResult($controller, $builder, $pathVariable['last'], $url, $analyzeRequestParam);
            if ($pathVariable['first'] == 'things' && $pathVariable['last'] == 'actuator') {
                $ketQua = static::getStatusActuator($ketQua, $pathVariable['pathParams'][0]['id']);
            }
            // return response()->json($ketQua); v2.0
            return response()->json($ketQua, 200);
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
