<?php


namespace App\Http\Controllers\OGC;

use App\OGC\DataManipulation\EntityCreation;
use App\OGC\EntityGetter\Observation;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\HeaderBag;

//https://www.mscharhag.com/p/rest-api-design
class PostController extends Controller
{

    public function action(Request $request, $param = null): JsonResponse
    {
        $result = static::post($request->toArray(), $request->headers, $param);
        return self::prepareResponse($result);
    }
    protected static function prepareResponse(array $result): JsonResponse
    {
        if (isset($result["Location"])) {
            $location = ['Location' => $result["Location"]];
        } else {
            $location = [];
        }
        if (isset($result['data'])) {
            return response()->json($result['data'], $result['code'], $location);
        } else {
            return response()->json(['message' => 'nothing to do']);
        }
    }

    public static function post(array $request, HeaderBag $header, $param = null): array
    {
        if ($param != null) {
            $ec = new EntityCreation($request, $param, $header);
            try {
                return $ec->create();
            } catch (Exception $exception) {
                if ($exception->getCode() > 0 && $exception->getCode() < 600) {
                    $code = $exception->getCode();
                } else {
                    $code = 500;
                }
                return ['data' => ['message' => $exception->getMessage()], 'code' => $code];
            }
        }
        return ['data' => ['message' => 'invalid path'], 'code' => 400];
    }

    public static function sensorPost(Request $request): JsonResponse
    {
        $postArr = explode('/', $request->getRequestUri());
        $postString = $postArr[count($postArr) - 1];

        $ec = new EntityCreation($request->toArray(), $postString, $request->headers);
        try {
            $result = $ec->create();
        } catch (Exception $exception) {
            $result = ['data' => ['errorMessage' => $exception->getMessage()], 'code' => $exception->getCode()];
        }
        return self::prepareResponse($result);
    }
}
