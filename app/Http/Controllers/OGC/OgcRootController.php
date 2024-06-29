<?php


namespace App\Http\Controllers\OGC;


use App\Constant\TablesName;
use App\OGC\Helpers\EntityClasses;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OgcRootController
{
    public function action(): JsonResponse
    {
        $id=auth()->id();
        $arrCon=static::getConformance($id);
        $arrUrl=EntityClasses::entityList();

        $result=[
            'serverSettings'=>[
                'conformance'=>$arrCon,
                'value'=>$arrUrl
            ]
        ];
        return response()->json($result);
    }

    private static function getConformance(int $userId): array
    {
        $set=DB::table(TablesName::User_Role,'ur')
            ->join(TablesName::CONFORMANCE . ' as c','ur.roleId','=','c.roleId')
            ->where('ur.userId','=',$userId)
            ->get(['c.name']);
        $arr=[];
        foreach ($set as $item){
            array_push($arr,$item->name);
        }
        return $arr;
    }
}
