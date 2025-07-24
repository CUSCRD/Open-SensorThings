<?php


namespace App\API\EntityGetter;


use App\Constant\TablesName;
use Illuminate\Database\Query\Builder;

class MultiDataStream extends BaseEntity
{
    public const TABLE_NAME = TablesName::MULTI_DATA_STREAM;
    public const JOIN_NAME = 'ds';
    public const JOIN_GET = [
        self::JOIN_NAME . '.id',
        self::JOIN_NAME . '.name',
        self::JOIN_NAME . '.sensorId',
        self::JOIN_NAME . '.description',
        'ot1.value as observationType'
    ];
    public const PROPERTIES = [
        'id',
        'name',
        'description',
        'observationType'
    ];
    public const PATH_VARIABLE_NAME = 'datastreams';

    public static function selfBuilder(): Builder
    {
        return self::refObservationType();
    }

    public static function refObservationType(Builder $builder = null): Builder
    {
        if ($builder == null) {
            //            $builder=DB::table(static::TABLE_NAME,'ds');
            $builder = parent::selfBuilder();
        }
        return static::joinTable(
            $builder,
            static::JOIN_NAME,
            TablesName::OBSERVATION_TYPE,
            ObservationType::JOIN_NAME . '1',
            'observationType',
            'id'
        );
    }
    public static function toObservation(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        return static::joinTable($builder, static::JOIN_NAME, Observation::TABLE_NAME, Observation::JOIN_NAME, 'id', 'dataStreamId');
        //        return static::joinTable($builder,'dso',Observation::TABLE_NAME,'o','observationId','id');
    }
    public static function toMeasurementUnit(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        static::joinTable($builder, 'ds', TablesName::DATA_STREAM_MEASUREMENT_UNIT, 'dsmu', 'id', 'dataStreamId');
        return static::joinTable($builder, 'dsmu', TablesName::MEASUREMENT_UNIT, 'mu', 'unitId', 'id');
    }
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
            'thingId',
            'id'
        );
        //        return static::joinTable($builder,'dst',Thing::TABLE_NAME,Thing::JOIN_NAME,'thingId','id');
    }
    public static function toObservationDataType(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        $builder = static::joinTable(
            $builder,
            static::JOIN_NAME,
            TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE,
            'dsmot',
            'id',
            'dataStreamId'
        );
        return static::joinTable($builder, 'dsmot', ObservationType::TABLE_NAME, ObservationType::JOIN_NAME, 'observedType', 'id');
    }
    public static function toSensor(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        $builder = static::joinTable(
            $builder,
            static::JOIN_NAME,
            Sensor::TABLE_NAME,
            Sensor::JOIN_NAME,
            'sensorId',
            'id'
        );
        return Sensor::selfRef($builder);
    }

    public static function toObservedProperty(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::selfBuilder();
        }
        $builder = static::joinTable(
            $builder,
            static::JOIN_NAME,
            TablesName::DATA_STREAM_OBSERVED_PROPERTY,
            'dsop',
            'id',
            'dataStreamId'
        );

        return static::joinTable(
            $builder,
            'dsop',
            TablesName::OBSERVED_PROPERTY,
            'op',
            'dataStreamId',
            'id'
        );
    }

    public static function toLocation(Builder $builder = null): Builder
    {
        if ($builder == null) {
            $builder = static::toThing();
        }
        Thing::toLocation($builder);
        return $builder;
    }


    public static function joinTo(string $pathVariableItem, Builder $builder = null): Builder
    {
        switch ($pathVariableItem) {
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
            case Sensor::PATH_VARIABLE_NAME:
                $builder = static::toSensor($builder);
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $builder = static::toObservedProperty($builder);
                break;
            case Location::PATH_VARIABLE_NAME:
                $builder = static::toLocation($builder);
                break;
                //Tasking
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
    static function toDataStream(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
