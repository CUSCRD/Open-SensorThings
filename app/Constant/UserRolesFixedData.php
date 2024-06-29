<?php


namespace App\Constant;

class UserRolesFixedData extends BaseFixedData
{
    const ADMIN=['id'=>1,'name'=>'Admin'];
    const MOD=['id'=>2,'name'=>'Mod'];
    const REST_CREATE=['id'=>3,'name'=>'Post'];
    const REST_GET=['id'=>4,'name'=>'Get'];
    const REST_UPDATE=['id'=>5,'name'=>'Update'];
    const REST_DELETE=['id'=>6,'name'=>'Delete'];
    const REST_BATCH=['id'=>7,'name'=>'Batch'];
    const REST_SENSOR=['id'=>8,'name'=>'Sensor'];
}
