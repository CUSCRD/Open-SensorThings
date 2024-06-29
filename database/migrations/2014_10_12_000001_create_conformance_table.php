<?php


use App\Constant\TablesName;
use App\Constant\UserRolesFixedData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateConformanceTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::CONFORMANCE, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('roleId')->nullable(false);
            $table->string('name')->nullable(false);
            $table->foreign('roleId')->references('id')->on(TablesName::Roles)->cascadeOnDelete()->cascadeOnUpdate();
        });

        $conformance=[
             [UserRolesFixedData::REST_GET['id']=>"http://www.opengis.net/spec/iot_sensing/1.1/req/resource-path/resource-pathto-entities"],
             [UserRolesFixedData::REST_GET['id']=>"http://www.opengis.net/spec/iot_sensing/1.1/req/request-data"],
             [UserRolesFixedData::REST_CREATE['id']=>"http://www.opengis.net/spec/iot_sensing/1.1/req/create-update-delete/createentity"],
             [UserRolesFixedData::REST_CREATE['id']=>"http://www.opengis.net/spec/iot_sensing/1.1/req/create-update-delete/link-toexisting-entities"],
             [UserRolesFixedData::REST_CREATE['id']=>"http://www.opengis.net/spec/iot_sensing/1.1/req/create-update-delete/deepinsert"],
            [UserRolesFixedData::REST_CREATE['id']=>  "http://www.opengis.net/spec/iot_sensing/1.1/req/create-update-delete/deepinsert-status-code"],
            [UserRolesFixedData::REST_UPDATE['id']=>    "http://www.opengis.net/spec/iot_sensing/1.1/req/create-update-delete/updateentity"],
            [UserRolesFixedData::REST_DELETE['id']=>  "http://www.opengis.net/spec/iot_sensing/1.1/req/create-update-delete/deleteentity"],
        ];
        foreach ($conformance as $value){
            DB::table(TablesName::CONFORMANCE)->insert([
               'roleId'=>array_keys($value)[0],
               'name'=>array_values($value)[0]
            ]);
        }
    }
    public function down()
    {
        Schema::dropIfExists(TablesName::CONFORMANCE);
    }
}
