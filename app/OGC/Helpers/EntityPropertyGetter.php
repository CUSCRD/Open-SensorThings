<?php


namespace App\OGC\Helpers;

use App\OGC\EntityGetter\BaseEntity;
use Exception;
use Illuminate\Database\Query\Builder;

class EntityPropertyGetter
{

    //kiểm tra bảng đã được join chưa
    public static function hasJoin(Builder $Builder, $collectionName): bool
    {
        if($Builder->joins==null){
            return false;
        }else{
            foreach($Builder->joins as $JoinClause)
            {
                if($JoinClause->table == static::getTables($collectionName) . ' as '. static::getJoinName($collectionName))
                {
                    return true;
                }
            }
        }
        return false;
    }
    public static function getJoinName(string $pathName): ?string
    {
        try {
            return static::getControllerByCollectionName($pathName)::JOIN_NAME;
        }catch (Exception $exception){
            return null;
        }
    }
    public static function getJoinGet(string $pathName): ?array
    {
        try {
            return static::getControllerByCollectionName($pathName)::JOIN_GET;
        }catch (Exception $exception){
            return null;
        }
    }
    public static function getProperties(string $pathName):?array{
        try {
            return static::getControllerByCollectionName($pathName)::PROPERTIES;
        }catch (Exception $exception){
            return null;
        }
    }
    public static function getTables(string $pathName):?string{
        try {
            return static::getControllerByCollectionName($pathName)::TABLE_NAME;
        }catch (Exception $exception){
            return null;
        }
    }
    public static function isValidEntityPathName(string $collectionName): bool
    {
        try {
            if(static::getControllerByCollectionName($collectionName)!=null){
                return true;
            }else{
                return false;
            }
        }catch (Exception $exception){
            return false;
        }
    }
    private static function getControllerByCollectionName(string $name): ?BaseEntity
    {
        return EntityClasses::getController($name);
    }
}
