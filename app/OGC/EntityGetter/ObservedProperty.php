<?php


namespace App\OGC\EntityGetter;


use App\Constant\TablesName;
use Exception;
use Illuminate\Database\Query\Builder;

class ObservedProperty extends BaseEntity
{
    public const TABLE_NAME=TablesName::OBSERVED_PROPERTY;
    public const JOIN_NAME = 'op';
    public const JOIN_GET=
        [
            self::JOIN_NAME.'.id',
            self::JOIN_NAME.'.name',
            self::JOIN_NAME.'.definition',
            self::JOIN_NAME.'.description'
        ];
    public const PROPERTIES = [
        'id',
        'name',
        'definition',
        'description',
    ];
    public const PATH_VARIABLE_NAME = 'observedproperties';

    public static function toDataStream(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::selfBuilder();
        }
        static::joinTable($builder,
            static::JOIN_NAME,
            TablesName::DATA_STREAM_OBSERVED_PROPERTY,
            'dsop',
            'id',
            'observedPropertyId');
        static::joinTable($builder,
            'dsop',
            MultiDataStream::TABLE_NAME,
            'ds',
            'dataStreamId',
            'id');
        return $builder;
    }

    public static function toObservation(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toObservation($builder);
        return $builder;
    }

    public static function toMeasurementUnit(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toMeasurementUnit($builder);
        return $builder;
    }

    public static function toThing(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toThing($builder);
        return $builder;
    }

    public static function toSensor(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toSensor($builder);
        return $builder;
    }

    public static function toObservationType(Builder $builder): ?Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toObservationDataType($builder);
        return $builder;
    }
    public static function toObservedProperty(?Builder $builder):Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toObservedProperty($builder);
        return $builder;
    }
    public static function joinTo(string $pathVariableItem, Builder $builder = null): Builder
    {
        switch ($pathVariableItem){
            case MultiDataStream::PATH_VARIABLE_NAME:
                $builder=static::toDataStream($builder);
                break;
            case Observation::PATH_VARIABLE_NAME:
                $builder=static::toObservation($builder);
                break;
            case Thing::PATH_VARIABLE_NAME:
                $builder=static::toThing($builder);
                break;
            case Sensor::PATH_VARIABLE_NAME:
                $builder=static::toSensor($builder);
                break;
            case ObservationType::PATH_VARIABLE_NAME:
                $builder=static::toObservationType($builder);
                break;
            case MeasurementUnit::PATH_VARIABLE_NAME:
                $builder=static::toMeasurementUnit($builder);
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
//                $builder=static::toObservedProperty($builder);
                break;
        }
        return $builder;
    }

    /**
     * @throws Exception
     */
    static function toObservationDataType(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
