<?php


use App\Constant\TablesName;
use App\Constant\UserRolesFixedData;
use App\Models\User\UserRole;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRoleTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::User_Role, function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('userId')->nullable(false);
            $table->unsignedBigInteger('roleId')->nullable(false);
            $table->timestamps();
            $table->foreign('userId')->references('id')->on(TablesName::Users)->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('roleId')->references('id')->on(TablesName::Roles)->cascadeOnUpdate()->cascadeOnUpdate();
        });

        $array=UserRolesFixedData::getConstants();
        foreach ($array as $value){
            $ur=new UserRole([
                'userId'=>1,
                'roleId'=> $value['id']
            ]);
            $ur->save();
        }
        $ur=new UserRole(['userId'=>2,'roleId'=>UserRolesFixedData::REST_CREATE['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>3,'roleId'=>UserRolesFixedData::REST_UPDATE['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>4,'roleId'=>UserRolesFixedData::REST_DELETE['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>5,'roleId'=>UserRolesFixedData::REST_GET['id']]);
        $ur->save();

        $ur=new UserRole(['userId'=>6,'roleId'=>UserRolesFixedData::REST_BATCH['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>6,'roleId'=>UserRolesFixedData::REST_CREATE['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>6,'roleId'=>UserRolesFixedData::REST_UPDATE['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>6,'roleId'=>UserRolesFixedData::REST_DELETE['id']]);
        $ur->save();
        $ur=new UserRole(['userId'=>6,'roleId'=>UserRolesFixedData::REST_GET['id']]);
        $ur->save();

        $ur=new UserRole(['userId'=>7,'roleId'=>UserRolesFixedData::REST_SENSOR['id']]);
        $ur->save();


    }
    public function down()
    {
        Schema::dropIfExists(TablesName::User_Role);
    }
}
