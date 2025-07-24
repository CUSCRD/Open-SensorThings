<?php


namespace App\API\Helpers;

use App\API\EntityGetter\BaseEntity;
use App\API\EntityGetter\Observation;
use Exception;
use Illuminate\Database\Query\Builder;

class EntityQuery
{
    // protected const DEFAULT_STEP = 1; v2.0
    protected const DEFAULT_STEP = 5;


    /**
     * Truy vấn tùy chọn $orderby chỉ định thứ tự của các item được return từ service.
     * Giá trị của truy vấn $orderby NÊN là danh sách các biểu thức của các giá trị nguyên thủy dùng để sắp xếp và cách nhau bởi dấu phẩy.
     * Một trường hợp đặc biệt của một biểu thức như vậy là một đường dẫn thuộc tính kết thúc trên bằng thuộc tính với dữ liệu nguyên thủy.
     *
     * Biểu thức CÓ THỂ bao gồm hậu tố asc để sắp xếp tăng và desc để sắp xếp giảm, phân cách với tên thuộc tính bởi 1 hoặc nhiều dấu khoảng trống
     * Nếu asc hay desc không được chỉ định, service NÊN sắp xếp thuộc tính được chỉ định theo thứ tự tăng dần
     *
     * Giá trị null NÊN đến trước giá trị không null khi sắp xếp tăng dần và đứng cuối khi sắp theo thứ tự giảm dần
     * các item NÊN được sắp xếp bởi giá trị kết quả của biếu thức đầu tiên, và sau đó lấy nó sắp xếp theo biểu thức thứ 2, ...
     *
     * [Note: Adapted from OData 4.0-Protocol 11.2.5.2]
     *
     * http://www.opengis.net/spec/iot_sensing/1.0/req/request-data/orderby
     *
     * @throws Exception
     */
    public static function orderBy(BaseEntity $controller, Builder $builder, string $order, string $latestTablePathItem, string $defaultOrder = 'asc'): Builder
    {
        return EntityOrdering::orderBy($controller, $builder, $order, $latestTablePathItem, $defaultOrder);
    }

    /**
     * Truy vấn tùy chọn $top chỉ định giới hạn số item được return từ một tập hợp các entity. Giá trị của $top NÊN là số nguyên không âm.
     * Service NÊN return số item có sẵn không quá giá trị n được chỉ định
     *
     * Nếu không có sắp xếp duy nhất bởi tùy chọn $orderby, service NÊN đặt một thứ tự đối với các yêu cầu có bao gồm $top
     *
     * [Note: Adapted from OData 4.0-Protocol 11.2.5.3]
     *
     * Thêm nữa, nếu giá trị $top vượt qua giới hạn service-driven pagination, (vd số entity lớn nhất và service có thể return trong 1 lần response),
     * truy vấn tùy chọn $top NÊN bị loại bỏ và giới hạn của server-side pagination sẽ được đưa vào thay thế.
     */
    public static function top(Builder $builder, $top): Builder
    {
        if ($top != (int)$top || $top < 0) {
            throw new Exception("top value should be non-negative integer");
        } else {
            $builder->limit($top);
            return $builder;
        }
    }

    /**
     * Truy vấn tùy chọn $count với giá trị true chỉ ra tổng số item trong một bộ kết quả khớp với request được return cùng với kết quả
     * một truy vấn tùy chọn với giá trị false (hoặc không chỉ định), thì service không nên trả về count
     *
     * Service NÊN return HTTP Status code 400 Bad Request nếu giá trị không phải true hay false được chỉ định
     *
     * Truy vấn tùy chọn $count NÊN bỏ qua bất kì truy vấn $top, $skip, hoặc $expand, và NÊN return tổng số kết quả
     * trên các trang chỉ bao gồm những kết quả khớp với bất kì $filter nào. Client nên nhận biết rằng count được trả về kèm theo
     * có thể không chính xác với số lượng item được return, bởi vì độ trễ giữa lần count và enumerating giá trị gần nhất hoặc do
     * các tính toán không chuẩn xác của service
     *
     *  [Adapted from OData 4.0-Protocol 11.2.5.5]
     *
     * http://www.opengis.net/spec/iot_sensing/1.0/req/request-data/count
     *
     * @throws Exception
     */
    public static function countEntity(int $count, array $requestParameter = null): array
    {

        if (isset($requestParameter['count'])) {
            if ($requestParameter['count'] == 'true') {
                $countEntity = ['count' => $count];
            } else {
                if ($requestParameter['count'] != 'false') {
                    //return lỗi 400 bad request
                    throw new Exception('"count" query value must be true or false', 400);
                } else {
                    //false
                    $countEntity = [];
                }
            }
        } else {
            //không count
            $countEntity = [];
        }
        return $countEntity;
    }

    /**
     * Truy vấn tùy chọn $skip chỉ định số lượng item được truy vấn sẽ bị loại bỏ khỏi danh sách kết quả.
     * Giá trị của $skip NÊN là số nguyên n không âm.
     * Service NÊN return iten bắt đầu từ vị trí n + 1
     *
     * Khi $top và $skip được dùng cùng lúc, $skip sẽ được áp dụng trước, bất kể thứ tự xuất hiện ở request
     *
     * [Note: Adapted from OData 4.0-Protocol 11.2.5.4]
     * */
    public static function skip(Builder $builder, $skip): Builder
    {
        if ($skip == (int)$skip && $skip >= 0) {
            return $builder->skip($skip);
        } else {
            throw new Exception("skip value must be non-negative integer");
        }
    }


    /**
     * Response chỉ bao gồm một phần của kết quả truy vấn được bởi URL request NÊN chứa một link cho phép
     * truy xuất phần tiếp theo của bộ kết quả. Link này được gọi là nextLink; đại diện của nó là một định dạng cụ thể.
     * Phần kết quả cuối cùng SẼ KHÔNG chứa nextLink.
     *
     * nextLink chỉ ra rằng response chỉ là một tập con của tập kết quả truy vấn các entity hoặc tập các tham chiếu của entity.
     * Nó chứa một URL cho phép truy xuất tập con tiếp theo của tập hợp được yêu cầu.
     *
     * Các client SensorThings NÊN xem URL của nextLink như là opaque, và KHÔNG NÊN thêm query tùy chọn cho URL của nextLink.
     * Service có thể không cho phép thay đổi format yêu cầu cho các page tiếp theo sử dụng next link
     *
     * */
    public static function paging(string $path, int $max, array $query = null): array
    {
        if ($path == '') {
            return [];
        }
        if ($query == null) {
            $query = [];
        }
        if (!isset($query['top'])) {
            $query['top'] = static::DEFAULT_STEP;
        }
        if (isset($query['skip'])) {
            $skip = $query['skip'];
            $skip += $query['top'];
        } else {
            $skip = $query['top'];
        }
        if ($skip >= $max) {
            return [];
        } else {
            $query['skip'] = $skip;
        }
        $q = "";
        foreach ($query as $key => $value) {
            $q .= '$' . $key . '=' . $value . '&';
        }
        return ['nextLink' => rtrim($path, '/') . '?' . substr($q, 0, -1)];
    }
    /**
     * Theo tài liệu OGC,
     *
     * Ưu tiên áp dụng server-driven pagination:
     *
     * $filter, $count, $orderby, $skip, $top
     *
     * Sau khi áp dụng Server-driven pagination:
     *
     * $expand, $select
     *
     * Truy vấn tùy chọn $expand chỉ ra các entity liên quan được phép đưa vào.
     * Giá trị của truy vấn tùy chọn $expand NÊN là danh sách các thuộc tính navigation được phân cách bởi dấu phẩy.
     * Thêm nữa, mỗi thuộc tính navigation có thể được theo sau bởi dấu xẹt forward slash và navigation khác
     * để cho phép xác định mối liên hệ nhiều lớp (level)
     *
     * get query result sẽ là expand khi nó đệ quy
     *
     * http://www.opengis.net/spec/iot_sensing/1.0/req/request-data/expand
     *
     *
     * @throws Exception
     */
    public static function getQueryRequestResult(
        BaseEntity $controller,
        Builder $builder,
        string $closestCollectionName,
        string $requestUrl,
        array $requestParameter = null,
        string $expandId = null,
        string $rootCollectionName = null
    ): array {
        $resultSimpleQuery = static::simpleQuery($controller, $builder, $closestCollectionName, $requestParameter, $expandId, $rootCollectionName);
        $cloneBuilder = $resultSimpleQuery['cloneBuilder'];
        $max = $resultSimpleQuery['max'];
        $validateResult = static::validateFormatExpand($closestCollectionName, $requestParameter);
        $resultFormat = $validateResult['resultFormat'];
        $isExpand = $validateResult['isExpand'];
        $resultHandleSelect = EntitySelecting::handleSelectQuery($controller, $builder, $cloneBuilder, $closestCollectionName, $requestParameter, $resultFormat);
        $idRebuild = $resultHandleSelect['idRebuild'];
        $result = $resultHandleSelect['result'];
        // return ($result); v2.0
        $components = $resultHandleSelect['components'];
        $count = $resultHandleSelect['count'] ?? null;
        //có expand
        if ($isExpand) {
            static::expand($controller, $idRebuild, $cloneBuilder, $requestUrl, $closestCollectionName, $result, $requestParameter);
        }
        return static::exportResult($result, $requestUrl, $max, $requestParameter, $expandId, $components, $count);
    }

    /**
     * @throws Exception
     */
    private static function simpleQuery(BaseEntity $controller, Builder $builder, string $closestCollectionName, array $requestParameter = null, string $expandId = null, string $rootCollectionName = null): array
    {
        if (isset($requestParameter['filter']) && $requestParameter['filter'] != '') {
            EntityFiltering::filter2($controller, $builder, $requestParameter['filter'], $closestCollectionName);
        }
        if (isset($requestParameter['order']) && $requestParameter['order'] != '') {
            static::orderBy($controller, $builder, $requestParameter['order'], $closestCollectionName);
        } else {
            // $builder->orderBy('id', 'desc'); v2.0
            // nếu k có order trong query string thì để thứ tự mặc định khi lấy trong db ra để tránh lỗi
            // sử dụng mệnh đề ORDER BY với cột không nằm trong danh sách SELECT khi sử dụng DISTINCT
            // do đó nếu k chỉ định order thì sẽ k thực hiện gì cả
            // $builder->orderBy('id');
        }
        if (isset($requestParameter['top']) && $requestParameter['top'] != '') {
            if (is_numeric($requestParameter['top'])) {
                static::top($builder, $requestParameter['top']);
            } elseif (strtolower($requestParameter['top']) == 'all') {
                //do nothing
                //không chỉ định limit, lấy toàn bộ kết quả
            } else {
                throw new Exception('invalid $top parameter: ' . $requestParameter['top']);
            }
        } else {
            $builder->take(static::DEFAULT_STEP);
        }

        if ($expandId != null) {
            $builder->where(EntityPropertyGetter::getJoinName($rootCollectionName) . '.' . 'id', '=', $expandId)->distinct();
        }

        //skip/offset sẽ gây sai lệch cho truy vấn expand (do dựa vào id của entity để truy vấn tiếp)
        //nên phải sao chép lại builder
        $cloneBuilder = $builder->clone();
        $max = $builder->count();
        if (isset($requestParameter['skip']) && $requestParameter['skip'] != '') {
            static::skip($builder, $requestParameter['skip']);
        }
        return ['cloneBuilder' => $cloneBuilder, 'max' => $max];
    }

    /**
     * @throws Exception
     */
    private static function exportResult(array $result, string $requestUrl, int $max, array $requestParameter = null, bool $expandId = null, array $component = null, array $count = null): array
    {
        //count xảy ra vấn đề
        $countEntity = $count == null ? static::countEntity(count($result), $requestParameter) : $count;
        if ($expandId == null) {
            $result = ['value' => $result];
            // $result = [$result];
        }
        $paging = static::paging($requestUrl, $max, $requestParameter);
        if ($component != null) {
            return array_merge($countEntity, ['components' => $component], $result, $paging);
        } else {
            return array_merge($countEntity, $result, $paging);
        }
        // if ($component != null) {
        //     return array_merge($countEntity, ['components' => $component], $result);
        // } else {
        //     return array_merge($countEntity, $result);
        // } v2.0
    }
    /**
     * @throws Exception
     */
    private static function expand(
        BaseEntity $controller,
        array $idRebuild,
        Builder $cloneBuilder,
        string $requestUrl,
        string $closestCollectionName,
        array $result,
        array $requestParameter = null
    ) {

        $expandArr = $requestParameter['expand'];
        $expandArr = EntityPathRequest::separateExpand($expandArr);
        foreach ($expandArr as $item) {
            $itemExpand = EntityPathRequest::analyzeExpand($item);
            $countResult = count($idRebuild);
            for ($i = 0; $i < $countResult; $i++) {
                $tempBuilder = clone $cloneBuilder;
                $controller->paramBuilder($itemExpand['path']['pathParams'], $tempBuilder);

                //xóa entityid ở resource path nếu có
                $indexChar = strripos($requestUrl, '(');
                if ($indexChar) {
                    $requestUrl1 = substr($requestUrl, 0, $indexChar);
                } else {
                    $requestUrl1 = $requestUrl;
                }

                $resultExpand = static::getQueryRequestResult(
                    $controller,
                    $tempBuilder,
                    $itemExpand['path']['last'],
                    $requestUrl1 . "($idRebuild[$i])/" . EntityPathRequest::pathArrayToString($itemExpand['path']['pathParams']),
                    $itemExpand['queries'],
                    $idRebuild[$i],
                    $closestCollectionName
                );
                $entityName = $itemExpand['path']['last'];
                $result[$i]->$entityName = $resultExpand;
            }
        }
    }
    /**
     * @throws Exception
     */
    private static function validateFormatExpand(string $closestCollectionName, array $requestParameter = null): array
    {

        //kiểm tra xung đột resultFormat và expand
        if (isset($requestParameter['resultFormat']) && $requestParameter['resultFormat'] != null) {
            //nếu không làm việc với Observation thì sẽ ném ngoại lệ
            if ($closestCollectionName != Observation::PATH_VARIABLE_NAME) {
                throw new Exception('result format only working with Observations entities', 405);
            }
            $resultFormat = $requestParameter['resultFormat'];
            //xác thực result format
            // kiểm tra nó có là query hợp lệ không
            $validateRF = [
                'dataArray'
            ];
            if (!in_array($resultFormat, $validateRF)) {
                throw new Exception('invalid result format', 400);
            }
        } else {
            $resultFormat = null;
        }
        if (isset($requestParameter['expand']) && $requestParameter['expand'] != null) {
            if ($resultFormat != null) {
                throw new Exception('both expand and resultFormat are queried', 400);
            } else {
                $isExpand = true;
            }
        } else {
            $isExpand = false;
        }
        return ['resultFormat' => $resultFormat, 'isExpand' => $isExpand];
    }
}
