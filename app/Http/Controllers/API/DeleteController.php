<?php


namespace App\Http\Controllers\API;

use App\API\DataManipulation\EntityDeletion;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeleteController
{
    public function action(Request $request, $param = null): JsonResponse
    {
        try {
            $result = EntityDeletion::delete($request, $param);
            return response()->json($result['data'], $result['code']);
        } catch (Exception $exception) {
            return response()->json(['error' => $exception->getMessage()], $exception->getCode());
        }
    }
}
