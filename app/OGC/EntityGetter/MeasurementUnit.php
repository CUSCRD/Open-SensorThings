<?php


namespace App\OGC\EntityGetter;


use App\Constant\TablesName;
use Illuminate\Database\Query\Builder;

class MeasurementUnit extends BaseEntity
{
    public const TABLE_NAME=TablesName::MEASUREMENT_UNIT;
    public const JOIN_NAME = 'mu';
    public const JOIN_GET=
        [
            self::JOIN_NAME.'.id',
            self::JOIN_NAME.'.name',
            self::JOIN_NAME.'.symbol',
            self::JOIN_NAME.'.definition'
        ];
    public const PROPERTIES = [
        'id',
        'name',
        'symbol',
        'definition',
      ];
    public const PATH_VARIABLE_NAME = 'measurementunits';
    public static function toDataStream(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::selfBuilder();
        }
        static::joinTable($builder,
                                    static::JOIN_NAME,
                                    TablesName::DATA_STREAM_MEASUREMENT_UNIT,
                                    'dsmu',
                                    'id',
                                    'unitId');
        static::joinTable($builder,'dsmu',MultiDataStream::TABLE_NAME,'ds','dataStreamId','id');
        return MultiDataStream::refObservationType($builder);
    }
    public static function toObservation(Builder $builder=null): Builder
    {
        if($builder==null){
            $builder=static::toDataStream();
        }
        MultiDataStream::toObservation($builder);
        return $builder;
    }
    public static function toThing(Builder $builder=null): Builder
    {
        //trước khi tới được thing thì phải qua datastream từ trước
        //nếu không có thì kết nối qua datastream tại đây
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
    public static function toObservationDataType(Builder $builder=null): Builder
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

    public static function joinTo(string $pathVariableItem, Builder $builder=null): Builder
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
                $builder=static::toObservationDataType($builder);
                break;
            case ObservedProperty::PATH_VARIABLE_NAME:
                $builder=static::toObservedProperty($builder);
                break;
        }
        return $builder;
    }


    static function toMeasurementUnit(Builder $builder = null): Builder
    {
        throw new \Exception("cannot navigate " . static::PATH_VARIABLE_NAME . " to itself");
    }
}
