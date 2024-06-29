<?php


namespace App\API\Helpers;


use App\Constant\PathName;
use App\API\EntityGetter\BaseEntity;
use App\API\EntityGetter\MeasurementUnit;
use App\API\EntityGetter\MultiDataStream;
use App\API\EntityGetter\Observation;
use App\API\EntityGetter\ObservationType;
use App\API\EntityGetter\ObservedProperty;
use App\API\EntityGetter\Sensor;
use App\API\EntityGetter\Thing;
use App\API\EntityGetter\TaskingCapabilities;
use App\API\EntityGetter\Task;
use App\API\EntityGetter\Actuator;
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
    public const CLASSES = [
        MeasurementUnit::class,
        MultiDataStream::class,
        Observation::class,
        ObservationType::class,
        ObservedProperty::class,
        Sensor::class,
        Thing::class,
        TaskingCapabilities::class,
        Task::class,
        Actuator::class
    ];

    //tên trên đường dẫn
    private static $collectionName = null;

    //lấy instance của lớp dựa trên tên collection nó quản lý
    public static function getController(string $name): ?BaseEntity
    {
        if (static::$collectionName == null) {
            static::initCollectionName();
        }
        $key = array_search($name, static::$collectionName);
        if (is_numeric($key)) {
            $class = static::CLASSES[$key];
            return new $class();
        }
        throw new Exception('invalid path name', 400);
    }

    //khởi tạo 1 lần duy nhất
    private static function initCollectionName()
    {
        $collectionName = [];
        foreach (static::CLASSES as $entityController) {
            array_push($collectionName, $entityController::PATH_VARIABLE_NAME);
        }
        static::$collectionName = $collectionName;
    }

    public static function entityList(): array
    {
        $collection = Collection::empty();
        foreach (static::CLASSES as $item) {
            $controller = new $item();
            $array = [];
            $array['name'] = $controller::PATH_VARIABLE_NAME;
            $array['url'] = PathName::GET . '/' . $controller::PATH_VARIABLE_NAME;
            $collection->push($array);
        }
        return $collection->toArray();
    }
}
