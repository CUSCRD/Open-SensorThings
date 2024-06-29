<?php


namespace App\OGC\Helpers;


use App\Constant\PathName;
use App\OGC\EntityGetter\BaseEntity;
use App\OGC\EntityGetter\MeasurementUnit;
use App\OGC\EntityGetter\MultiDataStream;
use App\OGC\EntityGetter\Observation;
use App\OGC\EntityGetter\ObservationType;
use App\OGC\EntityGetter\ObservedProperty;
use App\OGC\EntityGetter\Sensor;
use App\OGC\EntityGetter\Thing;
use Exception;
use Illuminate\Support\Collection;

//tạo instance của lớp để gọi static của nó
class EntityClasses
{
    /**
     * Danh sách các lớp thừa kế base entity controller
     *
     * @var array
     */
    public const CLASSES=[
        MeasurementUnit::class,
        MultiDataStream::class,
        Observation::class,
        ObservationType::class,
        ObservedProperty::class,
        Sensor::class,
        Thing::class
    ];

    //tên trên đường dẫn
    private static $collectionName=null;

    //lấy instance của lớp dựa trên tên collection nó quản lý
    public static function getController(string $name):?BaseEntity{
        if(static::$collectionName==null){
            static::initCollectionName();
        }
        $key=array_search($name,static::$collectionName);
        if (is_numeric($key)){
            $class=static::CLASSES[$key];
            return new $class();
        }
        throw new Exception('invalid path name',400);
    }

    //khởi tạo 1 lần duy nhất
    private static function initCollectionName(){
        $collectionName=[];
        foreach (static::CLASSES as $entityController){
            array_push($collectionName,$entityController::PATH_VARIABLE_NAME);
        }
        static::$collectionName=$collectionName;
    }
    public static function entityList(): array
    {
        $collection=Collection::empty();
        foreach (static::CLASSES as $item){
            $controller=new $item();
            $array=[];
            $array['name']=$controller::PATH_VARIABLE_NAME;
            $array['url']=PathName::GET . '/' . $controller::PATH_VARIABLE_NAME;
            $collection->push($array);
        }
        return $collection->toArray();
    }
}
