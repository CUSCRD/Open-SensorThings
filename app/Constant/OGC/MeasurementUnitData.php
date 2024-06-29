<?php


namespace App\Constant\OGC;


use App\Constant\BaseFixedData;

class MeasurementUnitData extends BaseFixedData
{
    const C_DEGREE=array(
        'name'=>'degree Celsius',
        'symbol'=>'°C',
        'definition'=>'http://unitsofmeasure.org/ucum.html#para-30',
    );
    const PERCENT=array(
        'name'=>'percent',
        'symbol'=>'%',
        'definition'=>'http://unitsofmeasure.org/ucum.html#para-29'
    );
}
