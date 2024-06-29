<?php


namespace App\OGC\Helpers;


use App\OGC\EntityGetter\BaseEntity;
use App\OGC\EntityGetter\MultiDataStream;
use App\OGC\EntityGetter\Observation;
use Exception;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class EntitySelecting
{

    /**
     * lấy kết quả từ builder
     *
     * ngoại lệ xuất phát từ select
     */
    public static function handleSelectQuery(BaseEntity $controller, Builder $builder, Builder $cloneBuilder, string $closestCollectionName, array $requestParameter = null, string $resultFormat = null): array
    {

        $joinName = EntityPropertyGetter::getJoinName($closestCollectionName);
        $ids = ((clone $builder))->distinct()->get($joinName . '.' . 'id');
        $idRebuild = [];

        foreach ($ids as $itemIds) {
            array_push($idRebuild, $itemIds->id);
        }
        if (isset($requestParameter['select'])) {
            $select = $requestParameter['select'];
        } else {
            $select = implode(',', EntityPropertyGetter::getProperties($closestCollectionName));
        }

        $result = static::select($controller, $cloneBuilder, $select, $closestCollectionName, $resultFormat, $idRebuild, $closestCollectionName, $requestParameter);

        if (isset($result['components']) && $result['components'] != null) {
            $components = $result['components'];
        } else {
            $components = null;
        }
        $count = $result['count'] ?? null;

        $result = $result['data'];
        return ['components' => $components, 'count' => $count, 'result' => $result, 'idRebuild' => $idRebuild];
    }
    /**
     * lấy toàn bộ thuộc tính của một collection
     * @throws Exception
     */
    protected static function getAllEntityProperties(array $get, string $lastSelectedItemPathItem, array $newSelected): array
    {
        return static::regexGetEntityProperty($get, $lastSelectedItemPathItem, $newSelected);
    }
    /**
     * lấy thuộc tính cụ thể ở sub-collection
     * @throws Exception
     */
    protected static function getSingleProperty(array $pathArray, int $countPathArray, string $lastSelectedItemPathItem, array $newSelected): array
    {
        $collectionName = $pathArray[$countPathArray - 2];
        $property = EntityPropertyGetter::getProperties($collectionName);
        if ($property != null) {
            if (in_array($lastSelectedItemPathItem, $property)) {
                $key = array_search($lastSelectedItemPathItem, $property);
                $newSelected = static::regexGetEntityProperty(EntityPropertyGetter::getJoinGet($collectionName)[$key], $collectionName, $newSelected);
            } else {
                throw new Exception('invalid property', 400);
            }
        } else {
            throw new Exception('invalid collection name', 400);
            // không phải bảng, không phải thuộc tính của bất cứ bảng nào
        }
        return $newSelected;
    }

    /**
     * source có thể là array hoặc string
     *
     *   ds.id đổi thành ds.id as dataStreamId
     *   ds.name đổi thành ds.name as ds.dataStreamName
     *   ds.description
     *   ot1.value as observationType đổi thành ot1.value as datastreamObservationType
     *
     *   ...
     *
     * @throws Exception
     * lỗi ngoại lệ xảy ra khi không match pattern, lỗi xử lý ở server
     */
    protected static function regexGetEntityProperty($source, string $collectionName, array $newSelected): array
    {
        $regexPattern = '/((\w+)\.(\w+))( as (\w+))?/';
        preg_match_all($regexPattern, json_encode($source), $matches);
        if ($matches) {
            $countMatch = count($matches[0]);
            for ($i = 0; $i < $countMatch; $i++) {
                $joinColumnName = $matches[1][$i];
                $jsonDisplayedAttribute = $matches[5][$i] == '' ? $matches[3][$i] : $matches[5][$i];
                //uppercase first
                $jsonDisplayedAttribute = ucfirst($jsonDisplayedAttribute);
                $selectedString = $joinColumnName . ' as ' . $collectionName . $jsonDisplayedAttribute;
                array_push($newSelected, $selectedString);
            }
        } else {
            throw new Exception('regex not matches', 500);
        }
        return $newSelected;
    }
    /**
     * lấy thuộc tính ở nhiều sub-collection
     * @throws Exception
     */
    protected static function nestedSelection(BaseEntity $controller, Builder $builder, string $item, array $newSelected): array
    {
        //phần tử trong mảng selected không phải thuộc tính. nó có thể là toàn bộ bảng khác
        //chẳng hạn: Datastreams hoặc Observations/Datastreams
        $pathArray = explode('/', $item);
        $countPathArray = count($pathArray);
        //join vào hết
        foreach ($pathArray as $itemPath) {
            if (EntityPropertyGetter::isValidEntityPathName($itemPath)) {
                if (!EntityPropertyGetter::hasJoin($builder, $itemPath)) {
                    $controller::joinTo($itemPath, $builder);
                }
            }
        }
        //giả định phần tử cuối là toàn bộ bảng entity
        //lấy item cuối "path" phần tử mảng selected property
        $lastSelectedItemPathItem = $pathArray[$countPathArray - 1];
        //lấy các thuộc tính của collection sau cùng
        $get = EntityPropertyGetter::getJoinGet($lastSelectedItemPathItem);
        if ($get != null) {
            $newSelected = static::getAllEntityProperties($get, $lastSelectedItemPathItem, $newSelected);
        } else {
            //nếu get last entity == null thì phần tử này cũng không phải tên bảng, không phải là thuộc tính của bảng gốc
            //Datastreams/id
            // nếu có nhiều hơn 1 item
            if ($countPathArray > 1) {
                $newSelected = static::getSingleProperty($pathArray, $countPathArray, $lastSelectedItemPathItem, $newSelected);
            } else {
                // không phải bảng, không phải thuộc tính của bất cứ bảng nào
                throw new Exception('invalid entity property', 400);
            }
        }
        return $newSelected;
    }

    /**
     * Truy vấn tùy chọn select yêu cầu service chỉ trả về các thuộc tính cụ thể theo yêu cầu của client.
     * Giá trị của $select NÊN là danh sách các lựa chọn được phân cách bởi dấu phẩy.
     * Mỗi câu lựa chọn NÊN là tên thuộc tính (bao gồm navigation và tên thuộc tính của nó).
     * Ở kết quả response, service NÊN return nội dung được chỉ định nếu có, cùng với bất kì thuộc tính navigation được expand
     *
     * [Adapted from OData 4.0-Protocol 11.2.4.1]
     *
     * http://www.opengis.net/spec/iot_sensing/1.0/req/request-data/select
     *
     * @throws Exception
     */
    protected static function select(BaseEntity $controller, Builder $builder, string $stringSelect, string $baseTablePath, string $resultFormat = null, array $parentId = null, string $parentName = null, array $requestParameter = null): array
    {
        //chưa join thì join vào
        //join toàn bộ
        $pathArray = explode('/', $baseTablePath);
        foreach ($pathArray as $item) {
            if (EntityPropertyGetter::isValidEntityPathName($item)) {
                if (!EntityPropertyGetter::hasJoin($builder, $item)) {
                    $controller->joinTo($item, $builder);
                }
            }
        }
        $lastPathCollectionNameItem = $pathArray[count($pathArray) - 1];
        //đã join thì get kèm tên bảng
        $targetProperties = EntityPropertyGetter::getProperties($lastPathCollectionNameItem);
        $targetJoinGet = EntityPropertyGetter::getJoinGet($lastPathCollectionNameItem);
        $newSelected = [];

        $selectedProperty = explode(',', $stringSelect);
        foreach ($selectedProperty as $item) {
            if (in_array($item, $targetProperties)) {
                //có thuộc tính
                $key = array_search($item, $targetProperties);
                array_push($newSelected, $targetJoinGet[$key]);
            } else {
                $newSelected = static::nestedSelection($controller, $builder, $item, $newSelected);
            }
        }


        if ($resultFormat == null) {

            $arrayQuery = static::selectResultWithoutFormat($builder, $newSelected, $parentId, $parentName);
            $components = null;
            $count = null;
        } else {

            $components = static::getComponent($newSelected);
            $arrayQuery = static::selectResultWithFormat($builder, $newSelected, $resultFormat, $parentId, $parentName, $requestParameter);
            if (isset($arrayQuery['count'])) {
                $count = $arrayQuery['count'];
            } else {
                $count = null;
            }
            $arrayQuery = $arrayQuery['formatResult'];
        }

        return ['data' => $arrayQuery, 'components' => $components, 'count' => $count];
    }

    //lấy tên gọi thuộc tính từ mảng select
    protected static function getComponent(array $selectionArray): array
    {
        //sample mảng select: ["o.id","o.result","ds.name as datastreamsName"]
        //cách phân tách tên:
        //1. tách thành mảng với chuỗi phân cách là dấu khoảng trống, lấy item cuối, được o.id, o.result, datastreamsName
        //2. tách thành mảng với chuỗi phân cách là dấu chấm, lấy item cuối, được id, result, datastreamsName
        $step1 = [];
        //bước 1
        foreach ($selectionArray as $itemSelect) {
            $itemSelect = explode(' ', $itemSelect);
            array_push($step1, $itemSelect[count($itemSelect) - 1]);
        }
        //bước 2
        $step2 = [];
        foreach ($step1 as $itemSelect) {
            $itemSelect = explode('.', $itemSelect);
            array_push($step2, $itemSelect[count($itemSelect) - 1]);
        }
        return $step2;
    }
    //kết xuất kết quả không format
    protected static function selectResultWithoutFormat(Builder $builder, array $selectionArray, array $parentId = null, string $parentCollectionName = null): array
    {
        $arrayQuery = [];

        if ($parentId != null) {
            $parentJoinName = EntityPropertyGetter::getJoinName($parentCollectionName);
            foreach ($parentId as $itemId) {
                $tempBuilder = clone $builder;
                //phải sao chép nó
                //việc này sẽ biến đổi builder làm cho set kết quả bị thu hẹp lại
                $resultRow = $tempBuilder->distinct()->where($parentJoinName . '.id', '=', $itemId)->get($selectionArray);
                //                echo json_encode($parentJoinName.'.id'.'='.$itemId);
                //                echo "<hr>";
                $arrayQuery = array_merge($arrayQuery, $resultRow->toArray());
            }
        } else {
            $tempBuilder = clone $builder;
            $arrayQuery = $tempBuilder->distinct()->get($selectionArray)->toArray();
        }

        static::convertToJSON($selectionArray, $arrayQuery);
        return $arrayQuery;
    }
    //chuyển các trường dữ liệu dạng json sang cấu trúc mảng json
    protected static function convertToJSON(array $selectionArray, array $queryResult)
    {
        //chuyển đổi chuỗi sang json
        //kiểm tra thuộc tính json có tồn tại không
        $propertiesArray = [];
        $resultPropertyName = null;
        // foreach ($selectionArray as $item)
        foreach (array_reverse($selectionArray) as $item) {
            if (str_ends_with(strtolower($item), 'properties')) {
                //có thể có nhiều properties từ các entity khác nhau
                array_push($propertiesArray, static::getComponent([$item])[0]);
            } else {
                if (str_ends_with(strtolower($item), 'result')) {
                    $resultPropertyName = static::getComponent([$item])[0];
                }
            }
        }

        if ($resultPropertyName != null || count($propertiesArray) > 0) {
            foreach ($queryResult as $resultSetItem) {
                if (count($propertiesArray) > 0) {
                    foreach ($propertiesArray as $proItem) {
                        $resultSetItem->$proItem = json_decode($resultSetItem->$proItem, 1);
                    }
                }
                if ($resultPropertyName != null) {
                    $resultSetItem->$resultPropertyName = json_decode($resultSetItem->$resultPropertyName, 1);
                }
            }
        }
    }

    //thực hiện format dataArray

    protected static function buildDataArray(array $simpleResult, array $requestParameter = null): array
    {
        $result = Collection::empty();
        foreach ($simpleResult as $itemDS) {
            $navLink = 'get/' . MultiDataStream::PATH_VARIABLE_NAME . '(' . $itemDS['dsId'] . ')';
            $dataArray = [];
            $itemDS = $itemDS['observations'];
            foreach ($itemDS as $itemObs) {
                $itemObs = (array)$itemObs;
                $dataObservationItem = [];
                foreach ($itemObs as $key => $value) {
                    if ($key != 'result' && $key != 'properties') {
                        array_push($dataObservationItem, $value);
                    } else {
                        $obsResult = json_decode($value, true);
                        array_push($dataObservationItem, $obsResult);
                    }
                }
                array_push($dataArray, $dataObservationItem);
            }
            $count = EntityQuery::countEntity(count($dataArray), $requestParameter);
            $nav = [
                'dataStreamNavigation' => $navLink,
            ];
            $data = [
                'dataArray' => $dataArray,
            ];
            $data = array_merge($nav, $count, $data);
            $result->push($data);
        }
        return $result->toArray();
    }
    /**
     * @throws Exception
     */
    protected static function selectResultWithFormat(Builder $builder, array $selectionArray, string $format = 'dataArray', array $parentId = null, string $parentCollectionName = null, array $requestParameter = null): array
    {
        if ($format == 'dataArray') {
            $query = [];
            //chia theo datastreams
            $tempBuilderDS = clone $builder;
            //https://stackoverflow.com/questions/54902510/how-to-check-if-table-is-already-joined-in-laravel-query-builder
            //nếu bắt đầu từ datastream thì datastream sẽ không có trong danh sách join
            //nếu chọn Observation làm entity bắt đầu thì data stream không có trong danh sách join
            //nên phải kiểm tra cùng lúc. nếu cả 2 cùng không được join thì join vào
            //có 1 cách dễ hơn là kiểm tra join của bảng trung gian giữa ds và o
            //            if(!Collection::make($tempBuilderDS->joins)->pluck('table')->contains(TablesName::DATA_STREAM_OBSERVATION . ' as dso')){
            if (
                !EntityPropertyGetter::hasJoin($tempBuilderDS, Observation::PATH_VARIABLE_NAME) &&
                !EntityPropertyGetter::hasJoin($tempBuilderDS, MultiDataStream::PATH_VARIABLE_NAME)
            ) {
                //nó nhất định phải là Observation
                Observation::joinTo(MultiDataStream::PATH_VARIABLE_NAME, $tempBuilderDS);
            }
            if ($parentId != null) {
                $parentJoinName = EntityPropertyGetter::getJoinName($parentCollectionName);
                $tempBuilderDS->whereIn($parentJoinName . '.id', $parentId);
            }

            $dsSet = $tempBuilderDS->clone()->distinct()->get(MultiDataStream::JOIN_NAME . '.id');
            foreach ($dsSet as $itemDS) {
                $observationSet = $tempBuilderDS->clone()
                    ->where(MultiDataStream::JOIN_NAME . '.id', '=', $itemDS->id)
                    ->get($selectionArray)->toArray();
                //đã có observation
                //đưa vào datastream
                array_push($query, ['dsId' => $itemDS->id, 'observations' => $observationSet]);
            }

            $countEntity = EntityQuery::countEntity($tempBuilderDS->count(), $requestParameter);

            if (count($countEntity) > 0) {
                $result = ['formatResult' => static::buildDataArray($query, $requestParameter), 'count' => $countEntity];
            } else {
                $result = ['formatResult' => static::buildDataArray($query)];
            }
            return $result;
        } else {
            throw new Exception('not support format ' . $format, 405);
        }
    }
}
