<?php


namespace App\API\EntityGetter;


use App\Constant\TablesName;
use Exception;
use Illuminate\Database\Query\Builder;

class TaskingCapabilities extends BaseEntity
{
    public const TABLE_NAME = TablesName::TASKINGCAPABILITY;
    public const JOIN_NAME = 'taskingcap';
    public const JOIN_GET =
    [
        self::JOIN_NAME . '.id',
        self::JOIN_NAME . '.name',
        self::JOIN_NAME . '.description',
        self::JOIN_NAME . '.taskingParameters',
        self::JOIN_NAME . '.isAuto',
        // 'actuator1.value as actuator_id'
    ];
    public const PROPERTIES = [
        'id',
        'name',
        'description',
        'taskingParameters',
        'isAuto',
    ];
    public const PATH_VARIABLE_NAME = 'taskingcapability';
    public static function toActuator(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = parent::selfBuilder();
        }
        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            TablesName::ACTUATOR,
            Actuator::JOIN_NAME,
            'actuator_id',
            'id'
        );
    }

    static function toThing(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = self::selfBuilder();
        }
        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            Thing::TABLE_NAME,
            Thing::JOIN_NAME,
            'thing_id',
            'id'
        );
        // throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }

    static function toTask(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = self::selfBuilder();
        }
        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            Task::TABLE_NAME,
            Task::JOIN_NAME,
            'id',
            'id'
        );
    }
    public static function joinTo(string $pathVariableItem, Builder $builder = null): Builder
    {
        switch ($pathVariableItem) {
            case Thing::PATH_VARIABLE_NAME:
                $builder = static::toThing($builder);
                break;
            case Actuator::PATH_VARIABLE_NAME:
                $builder = static::toActuator($builder);
                break;
            case Task::PATH_VARIABLE_NAME:
                $builder = static::toTask($builder);
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
    public static function toTaskingCap(?Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
