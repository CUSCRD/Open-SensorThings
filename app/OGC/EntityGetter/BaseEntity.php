<?php


namespace App\OGC\EntityGetter;

use App\OGC\Helpers\EntityPropertyGetter;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

abstract class BaseEntity implements OgcEntityNavigation
{
    public const TABLE_NAME=null;//tên bảng trong CSDL
    public const PROPERTIES=null;//tên gọi của các thuộc tính
    public const JOIN_NAME=null;// bí danh lúc join
    public const JOIN_GET=null;//tên cột dữ liệu của entity này lúc lấy dữ liệu
    public const PATH_VARIABLE_NAME=null;//tên trên đường dẫn, còn được gọi là collection name


    public static function joinTable(Builder $builder,
                                    string $baseJoinName
                                    ,string $targetTable
                                    ,string $targetJoinName,
                                    string $baseField,
                                    string $targetField,
                                    string $operator='='): Builder
    {
        return $builder->join($targetTable . ' as '.$targetJoinName,
            $targetJoinName.'.'.$targetField,
            $operator,
            $baseJoinName.'.'.$baseField);
    }

    public static function selfBuilder(): Builder
    {
        return DB::table(static::TABLE_NAME,static::JOIN_NAME);
    }
    //ví dụ: observations/datastreams/things thì obs join datastream rồi join thing,
    //nếu có id observations(5)/datastreams(1)/things thì thên truy vấn where id cho entity nào được chỉ định
    public static function paramBuilder(array $tables,Builder $builder=null): ?Builder
    {
        $count=count($tables);
        if($builder==null){
            if($count>0){
                $collectionName=$tables[0]['name'];
                if(isset($tables[0]['id'])&&$tables[0]['id']!=null){
                    $id=$tables[0]['id'];
                }else{
                    $id=null;
                }
                //tên trên path là tên bảng của entity lớp này quản lý
                if($collectionName == static::PATH_VARIABLE_NAME){
                    $builder=static::selfBuilder();
                    if($id!=null){
                        $builder=$builder->where(EntityPropertyGetter::getJoinName($collectionName).'.id','=',$id);
                    }
                    //id không tồn tại
                    if($builder->count()==0){
                        throw new Exception('id is not exist',404);
                    }

                    if($count>1){
                        for ($i=1;$i<$count;$i++){
                            $collectionName=$tables[$i]['name'];
                            if(EntityPropertyGetter::isValidEntityPathName($collectionName)){
                                if(!EntityPropertyGetter::hasJoin($builder,$collectionName)){
                                    $builder=static::joinTo($collectionName,$builder);
                                }
                            }else{
                                throw new Exception('invalid entity path',400);
                            }
                            if(isset($tables[$i]['id'])&&$tables[$i]['id']!=null){
                                $id=$tables[$i]['id'];
                                if($id!=null){
                                    $builder=$builder->where(EntityPropertyGetter::getJoinName($collectionName).'.id','=',$id);

                                    //id ở sub path không tồn tại
                                    if($builder->count()==0){
                                        throw new Exception('id is not exist',404);
                                    }
                                }
                            }else{
                                $id=null;
                            }
                        }
                    }
                }else{
                    //item đầu tiên không hợp lệ
                    throw new Exception('invalid path',400);
                }
            }

        }else{
            foreach ($tables as $itemTable){
                $collectionName=$itemTable['name'];
                if(isset($itemTable['id'])&&$itemTable['id']!=null){
                    $id=$itemTable['id'];
                }else{
                    $id=null;
                }
                $builder->distinct=false;

                if(EntityPropertyGetter::isValidEntityPathName($collectionName)){

                    if(!EntityPropertyGetter::hasJoin($builder,$collectionName)){
//                        echo $collectionName;
                        $builder=static::joinTo($collectionName,$builder);
                    }
                }else{
                    throw new Exception('invalid entity path',400);
                }
//                echo json_encode((clone $builder)->get());
                if($id!=null){
                    $builder=$builder->where(EntityPropertyGetter::getJoinName($collectionName).'.id','=',$id);
                }
            }
        }
        return $builder;
    }
}
