<?php


namespace App\API\Helpers;

use App\API\EntityGetter\TaskingCapabilities;
use App\Constant\PathName;
use App\Constant\TablesName;
use App\Http\Controllers\API\GetController;
use App\Http\Controllers\API\IoTController;
use Exception;
use Illuminate\Support\Facades\DB;

class EntityPathRequest
{
    //đưa các request parameter vào mảng key - value
    public static function analyzeRequestParam($parameters = null): ?array
    {
        if ($parameters != null) {
            $analyzeResult = [];
            foreach ($parameters as $key => $value) {
                $analyzeResult[substr($key, 0)] = $value;
            }
            return $analyzeResult;
        }
        return null;
    }
    //phân tách các item expand
    public static function separateExpand(string $expand, string $separator = ',', bool $findExpand = false): array
    {
        //    echo $expand;
        //    echo '<br>';
        $countString = strlen($expand);
        $current = 0;
        $arr = [];
        $countLeftParentheses = 0;
        $countRightParentheses = 0;
        $countChar = 0;
        $countQuo = 0;
        for ($i = 0; $i < $countString; $i++) {
            $countChar++;
            if ($expand[$i] == '(') {
                $countLeftParentheses++;
            } elseif ($expand[$i] == ')') {
                $countRightParentheses++;
                if ($countLeftParentheses == $countRightParentheses) {;
                    $tempStr = substr($expand, $current, $countChar);
                    if ($findExpand) {
                        return [$tempStr];
                    }
                    $current = $i + 2;
                    array_push($arr, $tempStr);
                    $countChar = 0;
                }
            } elseif ($countLeftParentheses == $countRightParentheses) {
                if ($expand[$i] == $separator && $countQuo % 2 == 0) {
                    //tới đoạn phân cách bởi dấu phẩy và không nằm trong dấu nháy
                    array_push($arr, substr($expand, $current, $countChar - 1));
                    $countChar = 0;
                    $current = $i + 1;
                } elseif ($expand[$i] == '\'') {
                    $countQuo++;
                }
            }
        }
        if ($countChar > 0) {
            $lastString = substr($expand, $current);
            $lastString = rtrim($lastString, ')');
            array_push($arr, $lastString);
        }
        return $arr;
    }
    //xử lý 1 item expand
    public static function analyzeExpand(string $expand): array
    {
        $result = [];
        //chuỗi filter có chứa $expand sẽ gây ra lỗi
        //kiểm tra chẵn lẻ ' trước
        $expandPos = strpos($expand, '$expand');
        if ($expandPos) {
            $expandString = static::separateExpand(substr($expand, $expandPos), ';', true)[0];
            $pos = strpos($expand, $expandString);
            if ($pos !== false) {
                $expand = self::str_replace_once($expandString, '', $expand);
            }
        }
        $patternPath = '/(\w+(\/\w+)*)?\(?(\$((\w+)=((.*?)(\'(.*?)\')?))[;)])?/';
        preg_match_all($patternPath, $expand, $matchPath);
        if ($matchPath) {
            //            echo json_encode($matchPath);
            //            echo "--------------------------";
            $countMatch = count($matchPath[0]);
            $path = $matchPath[1][0];
            $pathArr = static::analyzeVariable($path);
            $result['path'] = $pathArr;
            $queries = [];
            for ($i = 0; $i < $countMatch; $i++) {
                if ($matchPath[5][$i] != '') {
                    $queryName = $matchPath[5][$i];
                    $queryValue = $matchPath[6][$i];
                    $queries[$queryName] = $queryValue;
                }
            }
            if (isset($expandString)) {
                $queries['expand'] = substr($expandString, 8);
            }
            $result['queries'] = $queries;
        }
        return $result;
    }
    //first
    //last
    //pathParams
    /**
     * @throws Exception
     */
    public static function analyzeIoTVariable(string $path): ?array
    {
        $pathArr = explode('/', $path);
        //mẫu param: param=["measurementunits(2)","datastreams(1)","selfLink","$value"]
        if ($pathArr) {
            $result = [];
            $pathParams = [];
            if (str_contains($pathArr[0], "task")) {
                if (!($id = static::hasNeededID($pathArr[0], 'actuator_id', 'thing_id'))) {
                    throw new Exception('something went wrong', 403);
                }

                $taskingCapID = IoTController::getTaskingCapID($id);
                if ($taskingCapID == null) {
                    throw new Exception('something went wrong', 403);
                }

                $pathArr[0] = 'task(' . $taskingCapID . ')';
            };

            if (str_contains($pathArr[0], "datastreams")) {
                if (!($id = static::hasNeededID($pathArr[0], 'sensor_id', 'thing_id'))) {
                    throw new Exception('something went wrong', 403);
                }
                $datastreamsID = IoTController::getDataStreamID($id);
                if ($datastreamsID == null) {
                    throw new Exception('something went wrong', 400);
                }
                $pathArr[0] = 'datastreams(' . $datastreamsID . ')';
            };

            foreach ($pathArr as $parameter) {
                if ($p = static::hasId($parameter)) {
                    array_push($pathParams, $p);
                } else {
                    array_push($pathParams, ['id' => null, 'name' => $parameter]);
                }
            }

            if (count($pathArr) == 0) {
                throw new Exception('invalid entity path', 400);
            }

            $result['last'] = $pathParams[count($pathArr) - 1]['name'];
            $result['first'] = $pathParams[0]['name'];
            $result['pathParams'] = $pathParams;
            return $result;
        }
        throw new Exception('invalid entity path', 400);
    }

    public static function analyzeVariable(string $path): ?array
    {
        $pathArr = explode('/', $path);
        //mẫu param: param=["measurementunits(2)","datastreams(1)","selfLink","$value"]
        if ($pathArr) {
            $result = [];
            $pathParams = [];
            foreach ($pathArr as $parameter) {
                if ($p = static::hasId($parameter)) {
                    array_push($pathParams, $p);
                } else {
                    array_push($pathParams, ['id' => null, 'name' => $parameter]);
                }
            }
            if (count($pathArr) > 0) {
                $result['last'] = $pathParams[count($pathArr) - 1]['name'];
                $result['first'] = $pathParams[0]['name'];
            } else {
                throw new Exception('invalid entity path', 400);
            }
            $result['pathParams'] = $pathParams;
            return $result;
        }
        throw new Exception('invalid entity path', 400);
    }
    //đảo ngược analyze variable
    public static function pathArrayToString(array $source): string
    {
        $result = '';
        foreach ($source as $item) {
            $result .= $item['name'];
            if (isset($item['id']) && $item['id'] != null) {
                $result .= '(' . $item['id'] . ')';
            }
            $result .= '/';
        }
        return substr($result, 0, -1);
    }
    //kiểm tra xem chuỗi có chứa id không
    public static function hasNeededID(string $path, $first, $second): ?array
    {
        $pattern = '/(.*)\(+(\d+),(\d+)\)$/';
        preg_match($pattern, $path, $matches);
        //["(2)","2"]
        if ($matches) {
            return [
                $first => $matches[2],
                $second => $matches[3]
            ];
        } else {
            return null;
        }
    }

    public static function hasId(string $path): ?array
    {
        $pattern = '/(.*)\(+(\d+)\)$/';
        preg_match($pattern, $path, $matches);
        //["(2)","2"]
        if ($matches) {
            return [
                'name' => $matches[1],
                'id' => $matches[2]
            ];
        } else {
            return null;
        }
    }
    //DML check

    /**
     * @throws Exception
     */
    public static function handleCreationPath(string $path): array
    {
        $parent = [];
        $analyze = EntityPathRequest::analyzeVariable($path);
        if (EntityPropertyGetter::isValidEntityPathName($analyze['last'])) {
            $targetEntity = $analyze['last'];
            $pathParams = $analyze['pathParams'];
            //đếm xem có bao nhiêu lớp
            $countArray = count($pathParams);
            //insert thẳng vào bảng dữ liệu
            if ($countArray == 1) {
            } else {
                //insert sub entity cho một entity được chỉ định
                //'last' là sub entity
                //entity đứng trước 'last' là entity được chọn, nó phải kèm theo id
                if ($countArray > 1) {
                    //entity đứng cuối có id -> ném ngoại lệ
                    if (isset($pathParams[$countArray - 1]['id']) && $pathParams[$countArray - 1]['id'] != null) {
                        throw new Exception('invalid path', 400);
                    }

                    //có id cho entity chủ
                    $tempEntity = $pathParams[$countArray - 2];
                    if (isset($tempEntity['id']) && $tempEntity['id'] != null) {
                        //chạy lệnh này xem có lỗi khi join không
                        $controller = GetController::getEntity($analyze['first']);
                        $controller->paramBuilder($analyze['pathParams']);

                        //nếu chạy được tới đây thì lấy entity chủ
                        $parent['id'] = $tempEntity['id'];
                        $parent['name'] = $tempEntity['name'];
                    } else {
                        throw new Exception('path id is missing', 400);
                    }
                } else {
                    //count == 0
                    throw new Exception('invalid path', 400);
                }
            }
        } else {
            //không phải các entity chính thức,
            //có thể là path CreateObservation tạo observation hàng loạt
            if ($analyze['last'] != PathName::CREATE_OBSERVATION) {
                throw new Exception('currently not support Entity ' . $analyze['last'], 400);
            } else {
                $targetEntity = PathName::CREATE_OBSERVATION;
            }
        }
        return [
            'target' => $targetEntity,
            'parent' => $parent
        ];
    }
    static function str_replace_once($str_pattern, $str_replacement, $string)
    {

        if (strpos($string, $str_pattern) !== false) {
            //            $occurrence = strpos($string, $str_pattern);
            return substr_replace($string, $str_replacement, strpos($string, $str_pattern), strlen($str_pattern));
        }

        return $string;
    }
}
