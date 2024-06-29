<?php


namespace App\Constant;


use ReflectionClass;

abstract class BaseFixedData
{
    public static function getConstants(): array
    {
        $reflectionClass = new ReflectionClass(static::class);
        return $reflectionClass->getConstants();
    }
}
