<?php


namespace App\OGC\EntityGetter;


use App\Constant\TablesName;
use Exception;
use Illuminate\Database\Query\Builder;

class Thing extends BaseEntity
{
    public const TABLE_NAME=TablesName::THING;
    public const JOIN_NAME = 't';
    public const JOIN_GET=
        [
            self::JOIN_NAME.'.id',
            self::JOIN_NAME.'.name',
            self::JOIN_NAME.'.description',
            self::JOIN_NAME.'.properties',
        ];
    public const PROPERTIES = [
        'id',
        'name',
        'description',
        'properties',
    ];
    public const PATH_VARIABLE_NAME = 'things';
    public static function toDataStream(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=self::selfBuilder();
        }
        static::joinTable($builder,
            static::JOIN_NAME,
            MultiDataStream::TABLE_NAME,
            MultiDataStream::JOIN_NAME,
            'id',
            'thingId');
//        static::joinTable($builder,'dst',MultiDataStream::TABLE_NAME,MultiDataStream::JOIN_NAME,'dataStreamId','id');
        return MultiDataStream::refObservationType($builder);
    }
    public static function toObservation(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        return MultiDataStream::toObservation($builder);
    }
    public static function toMeasurementUnit(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        return MultiDataStream::toMeasurementUnit($builder);
    }
    public static function toObservationDataType(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        return MultiDataStream::toObservationDataType($builder);
    }
    public static function toSensor(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        return MultiDataStream::toSensor($builder);
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
            case MeasurementUnit::PATH_VARIABLE_NAME:
                $builder=static::toMeasurementUnit($builder);
                break;
            case ObservationType::PATH_VARIABLE_NAME:
                $builder=static::toObservationDataType($builder);
                break;
            case Sensor::PATH_VARIABLE_NAME:
                $builder=static::toSensor($builder);
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $builder=static::toObservedProperty($builder);
                break;
        }
        return $builder;
    }

    /**
     * @throws Exception
     */
    static function toThing(Builder $builder = null): Builder
    {
        throw new Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
