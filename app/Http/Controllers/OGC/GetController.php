<?php


namespace App\Http\Controllers\OGC;

use App\Constant\PathName;
use App\OGC\EntityGetter\MeasurementUnit;
use App\OGC\EntityGetter\MultiDataStream;
use App\OGC\EntityGetter\Observation;
use App\OGC\EntityGetter\ObservationType;
use App\OGC\EntityGetter\ObservedProperty;
use App\OGC\EntityGetter\Sensor;
use App\OGC\EntityGetter\Thing;
use App\OGC\Helpers\EntityPathRequest;
use App\OGC\Helpers\EntityQuery;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetController
{
    /**
     * @throws Exception
     */
    public static function getEntity(string $entityPathName)
    {
        switch ($entityPathName){
            case MeasurementUnit::PATH_VARIABLE_NAME:
                $controller=new MeasurementUnit();
                break;
            case MultiDataStream::PATH_VARIABLE_NAME:
                $controller=new MultiDataStream();
                break;
            case Observation::PATH_VARIABLE_NAME;
                $controller=new Observation();
                break;
            case ObservationType::PATH_VARIABLE_NAME:
                $controller=new ObservationType();
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $controller=new ObservedProperty();
                break;
            case Sensor::PATH_VARIABLE_NAME:
                $controller=new Sensor();
                break;
            case Thing::PATH_VARIABLE_NAME:
                $controller=new Thing();
                break;
            default:
                throw new Exception('not supported entity root path',405);
        }
        return $controller;
    }
    public function action(Request $request, $path = null): JsonResponse
    {
        return
            static::get($request,$path);
    }

    private static function get(Request $request, $path = null): JsonResponse
    {
        //place this before any script you want to calculate time
//        $time_start = microtime(true);

        try {
            $pathVariable=EntityPathRequest::analyzeVariable($path);
            $controller=static::getEntity($pathVariable['first']);
            $builder=$controller->paramBuilder($pathVariable['pathParams']);
        }catch (Exception $exception){
            return response()->json(['error'=>$exception->getMessage()],$exception->getCode());
        }

        $arrayRequest=$request->toArray();
        $url=(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]" . '/' . PathName::GET .'/' . $path;
        $analyzeRequestParam=EntityPathRequest::analyzeRequestParam($arrayRequest);


        try {
            $ketQua=EntityQuery::getQueryRequestResult($controller,$builder,$pathVariable['last'],$url,$analyzeRequestParam);

//            $time_end = microtime(true);
//            $ketQua['executionTime']=$time_end-$time_start;

            return response()->json($ketQua);
        }catch (Exception $exception){
            $code=$exception->getCode();
            if (is_numeric($code) && $code>=100 && $code < 600){
                return response()->json(['error'=>$exception->getMessage()],$exception->getCode());
            }else{
                return response()->json(['error'=>$exception->getMessage()],400);
            }
        }
    }
}
