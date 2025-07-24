<?php


namespace App\Http\Controllers\API;


use App\Constant\TablesName;
use App\API\Helpers\EntityClasses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ApiRootController
{
    public function action(): JsonResponse
    {
        // $id = auth()->id(); v2.0
        // $arrCon = static::getConformance($id); v2.0
        $userToken = request()->header('token');
        $userId = DB::table('users')->where('remember_token', $userToken)->first('id')->id;

        $arrCon = static::getConformance($userId);
        $arrUrl = EntityClasses::entityList();

        $result = [
            'serverSettings' => [
                'conformance' => $arrCon,
                'value' => $arrUrl
            ]
        ];
        return response()->json($result);
    }

    private static function getConformance(int $userId): array
    {
        $set = DB::table(TablesName::User_Role, 'ur')
            ->join(TablesName::CONFORMANCE . ' as c', 'ur.roleId', '=', 'c.roleId')
            ->where('ur.userId', '=', $userId)
            ->get(['c.name']);
        $arr = [];
        foreach ($set as $item) {
            array_push($arr, $item->name);
        }
        return $arr;
    }
}
