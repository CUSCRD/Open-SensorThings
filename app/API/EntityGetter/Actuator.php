<?php


namespace App\API\EntityGetter;


use App\Constant\TablesName;
use Exception;
use Illuminate\Database\Query\Builder;

class Actuator extends BaseEntity
{
    public const TABLE_NAME = TablesName::ACTUATOR;
    public const JOIN_NAME = 'actuator';
    public const JOIN_GET =
    [
        self::JOIN_NAME . '.id',
        self::JOIN_NAME . '.name',
        self::JOIN_NAME . '.description',
        self::JOIN_NAME . '.encodingType',
    ];
    public const PROPERTIES = [
        'id',
        'name',
        'description',
        'encodingType',
    ];
    public const PATH_VARIABLE_NAME = 'actuator';
    public static function toTaskingCap(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = self::selfBuilder();
        }
        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            TaskingCapabilities::TABLE_NAME,
            TaskingCapabilities::JOIN_NAME,
            'id',
            'actuator_id'
        );
    }

    public static function toTask(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = self::selfBuilder();
        }
        static::joinTable(
            $builder,
            static::JOIN_NAME,
            TaskingCapabilities::TABLE_NAME,
            TaskingCapabilities::JOIN_NAME,
            'id',
            'actuator_id'
        );
        return TaskingCapabilities::toTask($builder);
    }

    static function toThing(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = self::selfBuilder();
        }
        static::joinTable(
            $builder,
            static::JOIN_NAME,
            TaskingCapabilities::TABLE_NAME,
            TaskingCapabilities::JOIN_NAME,
            'id',
            'actuator_id'
        );
        return TaskingCapabilities::toThing($builder);
    }
    public static function joinTo(string $pathVariableItem, Builder $builder = null): Builder
    {
        switch ($pathVariableItem) {
            case TaskingCapabilities::PATH_VARIABLE_NAME:
                $builder = static::toTaskingCap($builder);
                break;
            case Task::PATH_VARIABLE_NAME:
                $builder = static::toTask($builder);
                break;
            case Thing::PATH_VARIABLE_NAME:
                $builder = static::toThing($builder);
                break;
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
        }
        return $builder;
    }

    /**
     * @throws Exception
     */
    public static function toDataStream(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to DataStream");
    }
    public static function toObservation(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to Observation");
    }
    public static function toMeasurementUnit(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to MeasurementUnit");
    }
    public static function toObservationDataType(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to ObservationDataType");
    }
    public static function toSensor(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to Sensor");
    }
    public static function toObservedProperty(?Builder $builder): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to ObservedProperty");
    }

    static function toActuator(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
