<?php


namespace App\OGC\Helpers;


use App\OGC\EntityGetter\BaseEntity;
use Exception;
use Illuminate\Database\Query\Builder;

//do quá trình xử lý khá dài nên phương thức xử lý yêu cầu $filter được tách ra từ lớp static
class EntityFiltering
{
    /**
     * @throws Exception
     */
    //gắn bí danh bảng vào thuộc tính để truy xuất từ kết quả join
    public static function convertFilterProperty(BaseEntity $controller, Builder $builder, string $loopString, string $propertyPath, string $closestCollection): string
    {
        //datastreams/name
        //id
        $entityArray=explode('/',$propertyPath);
        $countArr=count($entityArray);
        if($countArr>0){
            //nếu phân tách được, lấy phần tử cuối cùng, nó là thuộc tính
            $entityProperty=$entityArray[$countArr-1];
            // có sub entity trước property
            if($countArr>1){
                for ($i=0;$i<$countArr-2;$i++){
//                    chưa joinEntity thì join vào
                    if(EntityPropertyGetter::isValidEntityPathName($entityArray[$i])){
                        if(!EntityPropertyGetter::hasJoin($builder,$entityArray[$i])){
                            $builder=$controller::joinTo($entityArray[$i],$builder);
                        }
                    }

                }
                // lấy tên entity chưa property
                $closestCollection=$entityArray[$countArr-2];
            }
//            gắn bí danh bảng và tên thuộc tính của nó
            $joinName=EntityPropertyGetter::getJoinName($closestCollection);
            $entityProperty=$joinName.'.'.$entityProperty;
            $loopString.=' ' . $entityProperty;
            return $loopString;
        }
        throw new Exception('cannot parse propertyPath',400);
    }

    //chuyển đổi sang ký tự so sánh cho filter
    public static function convertComparisonSymbol(string $comparison): string
    {
        switch ($comparison){
            case 'eq':
                return '=';
            case 'ne':
                return '!='; // không theo chuẩn ISO
//                hoặc <>, theo chuẩn ISO
            case 'gt':
                return '>';
            case 'ge':
                return '>=';
            case 'lt':
                return '<';
            case 'le':
                return '<=';
        }
        return '=';
    }
//    chuyển đổi sang ký hiệu phép toán
    public static function convertArithmeticSymbol(string $symbol): ?string
    {
        switch ($symbol){
            case 'add':
                return '+';
            case 'sub':
                return '-';
            case 'mul':
                return '*';
            case 'div':
                return '/';
            case 'mod':
                return '%';
        }
        return null;
    }


    /**
     * Truy vấn $filter cho phép client lọc kết quả thu được từ tập các Entity lấy được từ URL request.
     * Biểu thức được xác định bởi filter được ước tính cho mỗi entity trong collection, và chỉ những item nào
     * đáp ứng điều kiện thì mới được đưa vào kết quả response.
     * Các entity mà biểu thức ước lượng là false hoặc null, hay reference tới thuộc tính không được do không có permission,
     * NÊN bị bỏ qua khỏi kết quả response.
     *
     * [Adapted from Data 4.0-URL Conventions 5.1.1]
     *
     * Ngôn ngữ biểu thức được sử dụng cho các toán tử trong $filter NÊN hỗ trợ reference tới các thuộc tính và literal.
     * Giá trị literal NÊN là chuỗi được đặt trong dấu nháy đơn, số, giá trị boolean (true/false) hay giá trị ngày tháng
     * biểu thị theo chuẩn ISO 8601
     *
     * @throws \Exception
     */
    public static function filter2(BaseEntity $controller, Builder $builder, string $filter, string $closestEntity){
        $regexPattern='/(((\w+(\/(\w+))*)|((\w+)(\(([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?)\)))|(\'(.*?)\'))( ((add)|(sub)|(mul)|(div)|(mod)) ((\w+(\/(\w+))*)|((\w+)(\(([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?)\)))))?) ((eq)|(ne)|(gt)|(ge)|(lt)|(le)) ((\d+(.\d+)?)|(((\w+)(\(([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?)\)))|(\w+(\/(\w+))*)( ((add)|(sub)|(mul)|(div)|(mod)) (((\w+)(\(([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*(?:(\([^\(\)]*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?\))+?[^\(\)]*)*?)\)))|(\w+(\/(\w+))*)))?)|(\'(.*?)\'))( ((and)|(or)|(not)) )?/';
        preg_match_all($regexPattern, $filter, $matches);
        $countMatches=count($matches);
        if($countMatches>0){
            $count2=count($matches[0]);
            $result='';
            for($i=0;$i<$count2;$i++){
                //join
                $loopString='';
                if ($matches[3][$i]!=''){

                    $entityArr=explode('/',$matches[3][$i]);
                    foreach ($entityArr as $itemEntityArr){
                        if(EntityPropertyGetter::isValidEntityPathName($itemEntityArr)){
                            if (!EntityPropertyGetter::hasJoin($builder,$itemEntityArr)){
                                $controller::joinTo($itemEntityArr,$builder);
                            }
                        }
                    }
                    $first=$matches[3][$i];
                    $loopString=static::convertFilterProperty($controller,$builder,'',$first,$closestEntity);
                }else{
                    //string
                    if ($matches[20][$i]!=''){
                        $first=$matches[20][$i];
                    }else{
                        //built-in function
                        $first=BuiltInFunctions::analyzeBuildInFunctionString($matches[7][$i],$matches[9][$i],$controller,$builder,$closestEntity);
                    }

                    $loopString=$first;
                }

                // mul - 32 - gt - 57
                //23: mul
                //29: [number 32], [build-in function]
                //47 : gt
                if($matches[23][$i]!=''){
                    //build-in function
                    //31: [build-in function]
                    if ($matches[33][$i]!=''){
                        $pos29=BuiltInFunctions::analyzeBuildInFunctionString($matches[34][$i],$matches[36][$i],$controller,$builder,$closestEntity);

                        //
                    }else{
                        //30: [number] - [property]
                        $pos29=$matches[30][$i];
                        if (!is_numeric($pos29)){
                            static::convertFilterProperty($controller,$builder,$loopString,$pos29,$closestEntity);
                        }
                    }

                    $loopString.=' ' . static::convertArithmeticSymbol($matches[23][$i]) . ' ' . $pos29 . ' ' . static::convertComparisonSymbol($matches[47][$i]);
                    //57
                    //55 : [number 57]
                    if($matches[55][$i]!=''){
                        $loopString.=' ' . $matches[55][$i];
                    }elseif ($matches[57][$i]!=''){
                        //build-in function
                        if ($matches[58][$i]!=''){
                            $pos57=BuiltInFunctions::analyzeBuildInFunctionString($matches[59][$i],$matches[61][$i],$controller,$builder,$closestEntity);
                            $loopString.=' ' .$pos57;
                        }else{
                            //72:
                            // obs/id - sub - 5
                            //join các entity trung gian
                            $entityArr=explode('/',$matches[72][$i]);
                            foreach ($entityArr as $itemEntityArr){
                                if(EntityPropertyGetter::isValidEntityPathName($itemEntityArr)){
                                    if (!EntityPropertyGetter::hasJoin($builder,$itemEntityArr)){
                                        $controller::joinTo($itemEntityArr,$builder);
                                    }
                                }
                            }
                            $loopString=static::convertFilterProperty($controller,$builder,$loopString,$matches[72][$i],$closestEntity);

                            $loopString.=' ' . static::convertArithmeticSymbol($matches[76][$i]);// . ' ' . $matches[34][$i];

                            //97: number or property
                            if ($matches[97][$i]!=''){
                                $pos82=$matches[97][$i];
                                if (!is_numeric($pos82)){
                                    static::convertFilterProperty($controller,$builder,$loopString,$pos82,$closestEntity);
                                }else{
                                    $loopString.=' ' . $pos82;
                                }
                            }else{
                                //83: build-in function
                                $pos82=BuiltInFunctions::analyzeBuildInFunctionString($matches[84][$i],$matches[86][$i],$controller,$builder,$closestEntity);
                                $loopString.=' ' . $pos82;
                            }
                        }
                    }

                }elseif($matches[100][$i]!=''){
                    //100: [string]
                    $loopString.=' ' . static::convertComparisonSymbol($matches[47][$i]);
                    $loopString.=' ' . $matches[100][$i];
                }
                else{
                    // gt - 57 | observations/datastreams/name
                    $loopString.=' ' . static::convertComparisonSymbol($matches[47][$i]);
                    //72: observations/datastreams/name
                    if($matches[72][$i]!=''){
                        //join
                        $entityArr=explode('/',$matches[72][$i]);
                        foreach ($entityArr as $itemEntityArr){
                            if(EntityPropertyGetter::isValidEntityPathName($itemEntityArr)){
                                if (!EntityPropertyGetter::hasJoin($builder,$itemEntityArr)){
                                    $controller::joinTo($itemEntityArr,$builder);
                                }
                            }
                        }
                        $loopString=static::convertFilterProperty($controller,$builder,$loopString,$matches[72][$i],$closestEntity);
                        //76 : +-*/
                        if($matches[76][$i]!=''){
                            $loopString.=' ' . static::convertArithmeticSymbol($matches[76][$i]);// . ' ' . $matches[34][$i];
                        }

                        //97: number or property
                        if ($matches[97][$i]!=''){
                            $pos82=$matches[97][$i];
                            if (!is_numeric($pos82)){
                                static::convertFilterProperty($controller,$builder,$loopString,$pos82,$closestEntity);
                            }else{
                                $loopString.=' ' . $pos82;
                            }
                        }else{
                            //83: build-in function
//                            $loopString.=$matches[83][$i];
                            $pos82=BuiltInFunctions::analyzeBuildInFunctionString($matches[84][$i],$matches[86][$i],$controller,$builder,$closestEntity);
                            $loopString.=' ' . $pos82;
                        }
//                        $loopString.=' ' . $pos82;
                    }else{
                        if ($matches[55][$i]!=''){
                            $loopString.=' ' . $matches[55][$i];
                        }elseif ($matches[72]!=''){
                            $loopString.=' ' . static::convertFilterProperty($controller,$builder,$loopString,$matches[72],$closestEntity);
                        }ELSE{
                            //57: BUILD-IN function
                            //
                            $pos57=BuiltInFunctions::analyzeBuildInFunctionString($matches[59][$i],$matches[61][$i],$controller,$builder,$closestEntity);
                            $loopString.=' ' . $pos57;
                        }
                    }
                }
                if($matches[103][$i]!=''){
                    // and - or - not
                    $loopString.=' ' . $matches[103][$i] ;

                }
                $result.=$loopString;
            }

//            o.result * 32 > 57 and ds.name != ds.id and o.id % 2 = 7
            $builder->whereRaw($result);
        }
    }
}
