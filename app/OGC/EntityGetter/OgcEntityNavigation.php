<?php


namespace App\OGC\EntityGetter;


use Illuminate\Database\Query\Builder;

interface OgcEntityNavigation
{
    static function joinTo(string $pathVariableItem, Builder $builder=null):Builder;
    static function toDataStream(Builder $builder=null): Builder;
    static function toMeasurementUnit(Builder $builder=null): Builder;
    static function toObservation(Builder $builder=null): Builder;
    static function toObservationDataType(Builder $builder=null): Builder;
    static function toObservedProperty(?Builder $builder):Builder;
    static function toSensor(Builder $builder=null): Builder;
    static function toThing(Builder $builder=null): Builder;
}
