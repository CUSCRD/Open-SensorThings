<?php


namespace App\OGC\DataManipulation;


use App\Constant\TablesName;
use App\OGC\EntityGetter\MeasurementUnit;
use App\OGC\EntityGetter\MultiDataStream;
use App\OGC\EntityGetter\Observation;
use App\OGC\EntityGetter\ObservationType;
use App\OGC\EntityGetter\ObservedProperty;
use App\OGC\EntityGetter\Sensor;
use App\OGC\EntityGetter\Thing;
use App\OGC\Helpers\EntityPathRequest;
use App\OGC\Helpers\EntityPropertyGetter;
use App\Http\Controllers\OGC\GetController;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EntityModification
{
    /**
     * @throws Exception
     */
    public static function patch(Request $request, $pathVariable=null): array
    {

        try {
            $analyze=static::handleModificationPath($pathVariable);
        }catch (Exception $exception){
            return [
                'data'=>['error'=>$exception->getMessage()],
                'code'=>400
            ];
        }

        //của thing
        if (isset($request['properties'])){
            $request['properties']=json_encode($request['properties']);
        }

        //của observation
        if ($analyze['entity']==Observation::PATH_VARIABLE_NAME){
            if(isset($request['result'])){
                $request['result']=json_encode($request['result']);
            }
        }

        DB::beginTransaction();
        try {
            $id=$analyze['id'];
            $updateArray=$request->toArray();
            switch ($analyze['entity']){
                case MeasurementUnit::PATH_VARIABLE_NAME:
                case Observation::PATH_VARIABLE_NAME:
                case ObservationType::PATH_VARIABLE_NAME:
                case ObservedProperty::PATH_VARIABLE_NAME:
                case Thing::PATH_VARIABLE_NAME:
                case 'encodingtypes':
                    break;
                    //các entity có thuộc tính dạng danh sách
                case MultiDataStream::PATH_VARIABLE_NAME:
                    $updateArray=static::updateDataStream($id,$updateArray);
                    break;
                case Sensor::PATH_VARIABLE_NAME:
                    $updateArray=static::updateSensor($updateArray);
                    break;

                default:
                    throw new Exception('path param ' .$analyze['entity'] . ' is not supported',405);
            }

            $entity=EntityPropertyGetter::getTables($analyze['entity']);
            if (count($updateArray)>0){
                static::update($entity,$id,$updateArray);
            }
            DB::commit();
            //thành công
            return [
                'data'=>null,
                'code'=>204
            ];
        }catch (Exception $exception){
            DB::rollBack();
            throw new Exception($exception->getMessage(),$exception->getCode());
        }
    }

    public static function update(string $entityTable,int $id,array $update){
        try {
            DB::table($entityTable)->where('id','=',$id)->update($update);
        }catch (Exception $exception){
            throw new Exception('cannot update entity ' . $entityTable,406);
        }
    }

//    {
//    "name": "updated datastream",
//    "description": "this datastream has been updated.",
//    "Thing":{"id":2}
//}

    private static function updateSensor(array $update): array
    {
        if (isset($update['encodingType']) ){
            if ( is_string($update['encodingType'])){
                $id=DB::table(TablesName::ENCODING_TYPE)->where('value','=',$update['encodingType'])->get('id');
                if (count($id)>0){
                    $update['encodingType']=$id[0]->id;
//                    return $update;
                }else{
                    throw new Exception('encoding type is not exists',404);
                }
            }else{
                throw new Exception('invalid encoding type data',400);
            }
        }
        return $update;
    }

    /**
     * @throws Exception
     */
    public static function updateDataStream(int $id, array $update): array
    {
        //cập nhật theo tên đơn vị
        if(key_exists('unitOfMeasurement',$update)){
            $unitOfMeasurement=$update['unitOfMeasurement'];
            unset($update['unitOfMeasurement']);
            if($unitOfMeasurement!=null){
                DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)->where('dataStreamId','=',$id)->delete();
                foreach ($unitOfMeasurement as $item){
                    //tìm trong DB
                    $queryUnit=DB::table(TablesName::MEASUREMENT_UNIT)->where('id','=',$item['id'])->get(['id']);
                    if(count($queryUnit)>0){
                        $unitId=$queryUnit[0]->id;
                        DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)->insert([
                            'unitId'=>$unitId,
                            'dataStreamId'=>$id
                        ]);
                    }else{
                        throw new Exception('measurement unit id is not exist',404);
                    }
                }
            }
        }

        //danh sách thuộc tính đo lường
        if(key_exists('ObservedProperty',$update)){
            $observedProperty=$update['ObservedProperty'];
            unset($update['ObservedProperty']);
            $queryOp=DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->where('dataStreamId','=',$id)->get(['id']);

            if($observedProperty!=null){
                foreach ($observedProperty as $item){
                    $queryOP=DB::table(TablesName::OBSERVED_PROPERTY)->where('id','=',$item['id'])->get(['id']);
                    if(count($queryOP)>0){
                        $oPId=$queryOP[0]->id;
                        DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->insert([
                            'observedPropertyId'=>$oPId,
                            'dataStreamId'=>$id
                        ]);
                    }else{
                        throw new Exception('Observed property id is not exist',404);
                    }
                }
            }
            $arrOpId=[];
            foreach ($queryOp as $opi){
                array_push($arrOpId,$opi->id);
            }
            DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)->delete($arrOpId);
        }

        //các trường trong bảng

        if (isset($update['observationType']) && $update['observationType']!=null){
            if (is_string($update['observationType'])){
                $idObservationType=DB::table(ObservationType::TABLE_NAME)->where('value','=',$update['observationType'])->get('id');
                if (count($idObservationType)>0){
                    $update['observationType']=$idObservationType[0]->id;
                }else{
                    throw new Exception('observation value is not exists',404);
                }
            }else{
                throw new Exception('invalid observation type');
            }
        }

        return $update;
//        try {
//            if(count($update)>0){
//                static::update(TablesName::MULTI_DATA_STREAM,$id,$update);
//            }
//        }catch (Exception $exception){
//            //lỗi xảy ra khi observationType chưa tồn tại
//            throw new Exception($exception->getMessage(),$exception->getCode());
//        }
    }

    //không cần thiết kế phương thức cập nhật sensor sở hữu nhiều datastream như datastream có nhiều measurementunit
    //thing cũng tương tự vậy, không cần thiết cập nhật danh sách data stream của nó

    public static function handleModificationPath(string $path=null): array
    {
        if($path==null){
            throw new Exception('path is required',400);
        }
        $analyze=EntityPathRequest::analyzeVariable($path);

        $pathParams=$analyze['pathParams'];
        $countArray=count($pathParams);
        if($countArray>0){
            //entity cuối có kèm id
            if(isset($pathParams[$countArray-1]['id']) && $pathParams[$countArray-1]['id']!=null){
                $targetEntity=$analyze['last'];
                if(EntityPropertyGetter::isValidEntityPathName($targetEntity)){
                    //join để kiểm tra hợp lệ
                    $controller=GetController::getEntity($analyze['first']);
                    $controller->paramBuilder($analyze['pathParams']);

                    return [
                        'entity'=>$targetEntity,
                        'id'=>$pathParams[$countArray-1]['id']
                    ];
                }else{
                    throw new Exception('invalid entity',400);
                }
            }else{
                throw new Exception('invalid updating path',400);
            }
        }else{
            throw new Exception('invalid updating path',400);
        }
    }
}
