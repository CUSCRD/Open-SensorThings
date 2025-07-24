<?php


namespace App\API\EntityGetter;


use Illuminate\Database\Query\Builder;

interface ApiEntityNavigation
{
    static function joinTo(string $pathVariableItem, Builder $builder = null): Builder;
    static function toDataStream(Builder $builder = null): Builder;
    static function toMeasurementUnit(Builder $builder = null): Builder;
    static function toObservation(Builder $builder = null): Builder;
    static function toObservationDataType(Builder $builder = null): Builder;
    static function toObservedProperty(?Builder $builder): Builder;
    static function toSensor(Builder $builder = null): Builder;
    static function toThing(Builder $builder = null): Builder;
    static function toLocation(Builder $builder = null): Builder;
    static function toActuator(Builder $builder = null): Builder;
    static function toTaskingCap(Builder $builder = null): Builder;
    static function toTask(Builder $builder = null): Builder;
}
