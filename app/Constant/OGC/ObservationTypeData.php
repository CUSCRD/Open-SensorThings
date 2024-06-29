<?php


namespace App\Constant\OGC;


use App\Constant\BaseFixedData;

class ObservationTypeData extends BaseFixedData
{
    const COMPLEX_OBSERVATION=ARRAY(
        'code'=>'OM_ComplexObservation',
        'codeValue'=>'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_ComplexObservation',
        'result'=>'Any'
    );
    const CATEGORY_OBSERVATION = array(
        'code'=>'OM_CategoryObservation',
        'codeValue'=>'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_CategoryObservation',
        'result'=>'URI'
    );
    const COUNT_OBSERVATION = array(
        'code'=>'OM_CountObservation',
        'codeValue'=>'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_CountObservation',
        'result'=>'integer'
    );
    const MEASUREMENT = array(
        'code'=>'OM_Measurement',
        'codeValue'=>'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_Measurement',
        'result'=>'double'
    );
    const GENERAL_OBSERVATION = array(
        'code'=>'OM_Observation',
        'codeValue'=>'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_Observation',
        'result'=>'Any'
    );
    const TRUTH_OBSERVATION = array(
        'code'=>'OM_TruthObservation',
        'codeValue'=>'http://www.opengis.net/def/observationType/OGC-OM/2.0/OM_TruthObservation',
        'result'=>'boolean'
    );
}
