<?php


namespace App\API\Helpers;


use App\API\EntityGetter\BaseEntity;
use Exception;
use Illuminate\Database\Query\Builder;

class BuiltInFunctions
{
    protected const jsonExtract = 'value_at_json_index';
    //danh sách các built-in function được hỗ trợ
    protected static array $supportedFunction = [
        self::jsonExtract
    ];

    /**
     * params: chuỗi các giá trị được truyền cho hàm trong csdl. ví dụ: result,8 hoặc 6,4 hoặc ...
     *
     * closestEntity: entity có cột chứa dữ liệu dạng json
     *
     * cast: ép kiểu dữ liệu của phần tử chuỗi json trong csdl
     *
     * phương thức này dùng để lấy giá trị trong chuỗi có dạng mảng json
     *
     * ví dụ mảng các phần tử đơn giản: [50,25], ['medium',58.5,69,true]
     *
     * mảng $params gồm 2 phần tử:
     *
     * Phần tử thứ nhất là tên thuộc tính. ví dụ: id, result, datastreams/id
     *
     * Phần tử thứ hai là vị trí trong mảng json, bắt đầu từ 0
     **/
    protected static function jsonValueAtIndex(array $params, BaseEntity $controller, Builder $builder, string $closestEntity): string
    {

        if (count($params) < 4) {
            $property = $params[0];
            $index = $params[1];
            if (!(is_numeric($index) && $index > -1)) {
                throw new Exception("invalid index");
            }
            $entityArr = explode('/', $property);
            foreach ($entityArr as $itemEntityArr) {
                if (EntityPropertyGetter::isValidEntityPathName($itemEntityArr)) {
                    if (!EntityPropertyGetter::hasJoin($builder, $itemEntityArr)) {
                        $controller::joinTo($itemEntityArr, $builder);
                    }
                }
            }
            $countEntity = count($entityArr);
            if ($countEntity > 1) {
                if (EntityPropertyGetter::isValidEntityPathName($entityArr[$countEntity - 2])) {
                    $closestEntity = $entityArr[$countEntity - 1];
                }
            }

            if (!in_array($entityArr[$countEntity - 1], EntityPropertyGetter::getProperties($closestEntity))) {
                throw new Exception("no such column name: " . $entityArr[$countEntity - 1] . " in " . $closestEntity . " table");
            }

            $columnName = EntityPropertyGetter::getJoinName($closestEntity) . "." . $entityArr[$countEntity - 1];
            if (isset($params[2]) && $params[2] != '') {
                $cast = $params[2];
            } else {
                $cast = null;
            }
            if ($cast != null) {
                return "cast(json_extract($columnName,'$[$index]') as $cast)";
            } else {
                return "json_extract($columnName,'$[$index]')";
            }
        } else {
            throw new Exception("invalid params");
        }
    }

    public static function analyzeBuildInFunctionString(string $functionName, string $parameters, BaseEntity $controller, Builder $builder, string $closestEntity): string
    {
        $functionName = strtolower($functionName);
        //hàm này được hỗ trợ
        if (in_array($functionName, static::$supportedFunction)) {
            $params = static::getParamArray($parameters);
            return static::processFunction($functionName, $params, $controller, $builder, $closestEntity);
        }
        return "";
    }

    protected static function getParamArray(string $parameters): array
    {
        $regexPattern = "/(\'(((\\\')?|.*?)*)\')|(\w+(\\\w+)*)/";
        preg_match_all($regexPattern, $parameters, $matches);
        $countMatches = count($matches);
        $paramsArr = [];
        if ($countMatches > 0) {
            $count2 = count($matches[0]);
            for ($i = 0; $i < $count2; $i++) {
                if ($matches[5][$i] != '') {
                    array_push($paramsArr, $matches[5][$i]);
                } elseif ($matches[1] != '') {
                    array_push($paramsArr, $matches[1][[$i]]);
                }
            }
        }
        return $paramsArr;
    }
    protected static function processFunction(string $functionName, array $params, BaseEntity $controller, Builder $builder, string $closestEntity): ?string
    {
        switch ($functionName) {
            case static::jsonExtract:
                return static::jsonValueAtIndex($params, $controller, $builder, $closestEntity);
        }
        throw new Exception("invalid built-in function name");
    }
}
