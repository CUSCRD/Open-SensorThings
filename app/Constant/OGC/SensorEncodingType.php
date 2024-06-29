<?php


namespace App\Constant\OGC;


use App\Constant\BaseFixedData;

class SensorEncodingType extends BaseFixedData
{
    const PDF=array(
        'encodingType'=>'PDF',
        'value'=>'application/pdf'
    );
    const SENSOR_ML=array(
        'encodingType'=>'SensorML',
        'value'=>'http://www.opengis.net/doc/IS/SensorML/2.0'
    );
}
