<?php


namespace App\Http\Controllers\API;


use App\API\DataManipulation\EntityModification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatchController
{
    public function action(Request $request, $param = null): JsonResponse
    {
        try {
            $result = EntityModification::patch($request, $param);
            return response()->json($result);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
