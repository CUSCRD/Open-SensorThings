<?php


namespace App\API\EntityGetter;


use App\Constant\TablesName;
use Illuminate\Database\Query\Builder;

class Observation extends BaseEntity
{
    public const TABLE_NAME = TablesName::OBSERVATION;
    public const JOIN_NAME = 'o';
    public const JOIN_GET = [
        self::JOIN_NAME . '.id',
        self::JOIN_NAME . '.result',
        self::JOIN_NAME . '.resultTime',
        self::JOIN_NAME . '.validTime',
    ];
    public const PROPERTIES = [
        'id',
        'result',
        'resultTime',
        'validTime',
    ];
    public const PATH_VARIABLE_NAME = 'observations';

    public static function toDataStream(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        static::joinTable(
            $builder,
            static::JOIN_NAME,
            MultiDataStream::TABLE_NAME,
            MultiDataStream::JOIN_NAME,
            'dataStreamId',
            'id'
        );
        return MultiDataStream::refObservationType($builder);
    }
    public static function toMeasurementUnit(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        static::joinTable(
            $builder,
            MultiDataStream::JOIN_NAME,
            TablesName::DATA_STREAM_MEASUREMENT_UNIT,
            'dsmu',
            'id',
            'dataStreamId'
        );
        return static::joinTable($builder, 'dsmu', MeasurementUnit::TABLE_NAME, MeasurementUnit::JOIN_NAME, 'unitId', 'id');
    }
    public static function toThing(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        return MultiDataStream::toThing($builder);
    }
    public static function toSensor(Builder $builder = null): Builder
    {
        if ($builder == null) {
            static::toDataStream();
        }
        return MultiDataStream::toSensor($builder);
    }
    public static function toObservationDataType(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        return MultiDataStream::toObservationDataType($builder);
    }


    public static function toObservedProperty(?Builder $builder): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        MultiDataStream::toObservedProperty($builder);
        return $builder;
    }

    public static function joinTo(string $pathVariableItem, Builder $builder = null): Builder
    {
        switch ($pathVariableItem) {
            case MultiDataStream::PATH_VARIABLE_NAME:
                $builder = static::toDataStream($builder);
                break;
            case MeasurementUnit::PATH_VARIABLE_NAME:
                $builder = static::toMeasurementUnit($builder);
                break;
            case Thing::PATH_VARIABLE_NAME:
                $builder = static::toThing($builder);
                break;
            case Sensor::PATH_VARIABLE_NAME:
                $builder = static::toSensor($builder);
                break;
            case ObservationType::PATH_VARIABLE_NAME:
                $builder = static::toObservationDataType($builder);
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $builder = static::toObservedProperty($builder);
                break;
                //tasking
            case Actuator::PATH_VARIABLE_NAME:
                $builder = static::toActuator($builder);
                break;
            case Task::PATH_VARIABLE_NAME:
                $builder = static::toTask($builder);
                break;
            case TaskingCapabilities::PATH_VARIABLE_NAME:
                $builder = static::toTaskingCap($builder);
                break;
        }
        return $builder;
    }

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

    static function toObservation(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
