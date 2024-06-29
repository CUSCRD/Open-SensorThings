<?php


namespace App\API\Helpers;


use App\API\EntityGetter\BaseEntity;
use Illuminate\Database\Query\Builder;

class EntityOrdering
{
    public static function orderBy(BaseEntity $controller, Builder $builder, string $order, string $entityOrderedPath, string $defaultOrder = 'asc'): Builder
    {
        $basePathArray = explode('/', $entityOrderedPath);
        $countBase = count($basePathArray);
        foreach ($basePathArray as $item) {
            if (EntityPropertyGetter::isValidEntityPathName($item)) {
                if (!EntityPropertyGetter::hasJoin($builder, $item)) {
                    $builder = $controller::joinTo($item, $builder);
                }
            }
        }
        $lastCollectionName = $basePathArray[$countBase - 1];
        $baseProperties = EntityPropertyGetter::getProperties($lastCollectionName);
        $baseJoinGet = EntityPropertyGetter::getJoinGet($lastCollectionName);

        $pattern = '/(((\w+)(\(([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?)\)))|(\w+(\/\w+)*))( ((desc)|(asc)))?/';
        preg_match_all($pattern, $order, $matches);
        if ($matches) {
            $countMatch = count($matches[0]);
            for ($i = 0; $i < $countMatch; $i++) {
                if ($matches[19][$i] != '') {
                    $direction = $matches[19][$i];
                } else {
                    //nếu không chỉ định thì mặc định là tăng
                    $direction = $defaultOrder;
                }

                //thuộc tính hoặc thuộc tính nằm ở sub entity
                if ($matches[16][$i] != '') {
                    $path = $matches[16][$i];
                    static::orderByProperty($controller, $builder, $baseProperties, $baseJoinGet, $path, $countBase, $direction);
                } else {
                    if ($matches[2][$i] != '') {
                        $functionName = $matches[3][$i];
                        $params = $matches[5][$i];
                        static::orderByBuiltInFunction($controller, $builder, $lastCollectionName, $functionName, $params, $direction);
                    }
                }
            }
        }
        return $builder;
    }
    protected static function orderByBuiltInFunction(
        BaseEntity $controller,
        Builder $builder,
        string $lastCollectionName,
        string $functionName,
        string $functionParams,
        string $direction
    ) {
        $buildInString = BuiltInFunctions::analyzeBuildInFunctionString($functionName, $functionParams, $controller, $builder, $lastCollectionName);

        $builder->orderByRaw($buildInString . ' ' . $direction);
    }
    protected static function orderByProperty(
        BaseEntity $controller,
        Builder $builder,
        array $baseProperties,
        array $baseJoinGet,
        string $path,
        int $countBase,
        string $direction
    ) {
        if ($countBase > 0) {
            //đã join thì get kèm tên bảng
            if (in_array($path, $baseProperties)) {
                $key = array_search($path, $baseProperties);
                $builder->orderBy($baseJoinGet[$key], strtolower($direction));
                //                            }
            } else {
                //có phân cấp path sub-collection của order
                $orderPathArray = explode('/', $path);
                $countOrderPath = count($orderPathArray);
                if ($countOrderPath > 0) {
                    foreach ($orderPathArray as $itemOrderPath) {
                        if (EntityPropertyGetter::isValidEntityPathName($itemOrderPath)) {
                            if (!EntityPropertyGetter::hasJoin($builder, $itemOrderPath)) {
                                $builder = $controller::joinTo($itemOrderPath, $builder);
                            }
                        }
                    }

                    if ($countOrderPath > 1) {
                        $property = $orderPathArray[$countOrderPath - 1];
                        $entity = $orderPathArray[$countOrderPath - 2];
                        $baseProperties = EntityPropertyGetter::getProperties($entity);

                        if (in_array($property, $baseProperties)) {
                            $key = array_search($path, $baseProperties);
                            //asc, desc
                            $builder->orderBy(EntityPropertyGetter::getJoinGet($entity)[$key], strtolower($direction));
                        }
                    }
                } else {
                    throw new \Exception('unknown error: exception while handling path ' . $path, 500);
                }
            }
        }
    }
}
