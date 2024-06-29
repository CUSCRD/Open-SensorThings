<?php


namespace App\API\EntityGetter;


use App\Constant\TablesName;
use Illuminate\Database\Query\Builder;

class Sensor extends BaseEntity
{
    public const TABLE_NAME = TablesName::SENSOR;
    public const JOIN_NAME = 's';
    public const JOIN_GET =
    [
        self::JOIN_NAME . '.id',
        self::JOIN_NAME . '.name',
        self::JOIN_NAME . '.description',
        'et.value as encodingType',
        self::JOIN_NAME . '.metadata',
    ];

    public const PROPERTIES = [
        'id',
        'name',
        'description',
        'encodingType',
        'metadata'
    ];

    public const PATH_VARIABLE_NAME = 'sensors';
    public static function selfBuilder(): Builder
    {
        $builder = parent::selfBuilder();
        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            TablesName::ENCODING_TYPE,
            'et',
            'encodingType',
            'id'
        );
    }
    public static function selfRef(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }

        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            TablesName::ENCODING_TYPE,
            'et',
            'encodingType',
            'id'
        );
    }
    public static function toDataStream(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        $builder = static::joinTable(
            $builder,
            static::JOIN_NAME,
            MultiDataStream::TABLE_NAME,
            MultiDataStream::JOIN_NAME,
            'id',
            'sensorId'
        );
        return MultiDataStream::refObservationType($builder);
    }
    public static function toThing(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        return MultiDataStream::toThing($builder);
    }
    public static function toObservation(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        return MultiDataStream::toObservation($builder);
    }
    public static function toMeasurementUnit(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toDataStream();
        }
        return MultiDataStream::toMeasurementUnit($builder);
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
                $builder = self::toDataStream($builder);
                break;
            case Thing::PATH_VARIABLE_NAME:
                $builder = static::toThing($builder);
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

    static function toSensor(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
