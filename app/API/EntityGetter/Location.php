<?php


namespace App\API\EntityGetter;


use App\Constant\TablesName;
use Exception;
use Illuminate\Database\Query\Builder;

class Location extends BaseEntity
{
    public const TABLE_NAME = TablesName::LOCATION;
    public const JOIN_NAME = 'l';
    public const JOIN_GET =
    [
        self::JOIN_NAME . '.id',
        self::JOIN_NAME . '.name',
        self::JOIN_NAME . '.description',
        self::JOIN_NAME . '.coordinates',
        self::JOIN_NAME . '.created_at',
        self::JOIN_NAME . '.updated_at',
    ];

    public const PROPERTIES = [
        'id',
        'name',
        'description',
        'coordinates',
        'created_at',
        'updated_at',
    ];

    public const PATH_VARIABLE_NAME = 'locations';

    public static function toThing(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }

        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            Thing::TABLE_NAME,
            Thing::JOIN_NAME,
            'id',
            'id_location'
        );
    }

    public static function toDataStream(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        return Thing::toDataStream($builder);
    }

    public static function toObservation(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        Thing::toDataStream($builder);
        return MultiDataStream::toObservation($builder);
    }

    public static function toMeasurementUnit(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        Thing::toDataStream($builder);
        return MultiDataStream::toMeasurementUnit($builder);
    }

    public static function toObservationDataType(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        Thing::toDataStream($builder);
        return MultiDataStream::toObservationDataType($builder);
    }

    public static function toSensor(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        Thing::toDataStream($builder);
        return MultiDataStream::toSensor($builder);
    }

    public static function toObservedProperty(?Builder $builder): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        Thing::toDataStream($builder);
        return MultiDataStream::toObservedProperty($builder);
    }
    public static function joinTo(string $pathVariableItem, Builder $builder = null): Builder
    {
        switch ($pathVariableItem) {
            case MultiDataStream::PATH_VARIABLE_NAME:
                $builder = static::toDataStream($builder);
                break;
            case Observation::PATH_VARIABLE_NAME:
                $builder = static::toObservation($builder);
                break;
            case MeasurementUnit::PATH_VARIABLE_NAME:
                $builder = static::toMeasurementUnit($builder);
                break;
            case ObservationType::PATH_VARIABLE_NAME:
                $builder = static::toObservationDataType($builder);
                break;
            case Sensor::PATH_VARIABLE_NAME:
                $builder = static::toSensor($builder);
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $builder = static::toObservedProperty($builder);
                break;
            case Thing::PATH_VARIABLE_NAME:
                $builder = static::toThing($builder);
                break;
                //Tasking 
            case TaskingCapabilities::PATH_VARIABLE_NAME:
                $builder = static::toTaskingCap($builder);
                break;
            case Task::PATH_VARIABLE_NAME:
                $builder = static::toTask($builder);
                break;
            case Actuator::PATH_VARIABLE_NAME:
                $builder = static::toActuator($builder);
                break;
        }
        return $builder;
    }

    /**
     * @throws Exception
     */

    static function toActuator(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to Actuator");
    }
    static function toTask(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to Task");
    }
    static function toTaskingCap(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to TaskingCapability");
    }
    static function toLocation(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
