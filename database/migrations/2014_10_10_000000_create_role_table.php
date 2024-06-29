<?php


use App\Constant\TablesName;
use App\Constant\UserRolesFixedData;
use App\Models\User\Role;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoleTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::Roles, function (Blueprint $table) {
            $table->id();
            $table->string('roleName');
            $table->timestamps();
        });

        $types= UserRolesFixedData::getConstants();
        foreach ($types as $name=>$value){
            $ut=new Role();
            $ut->id=$value['id'];
            $ut->roleName=$value['name'];
            $ut->save();
        }
    }
    public function down()
    {
        Schema::dropIfExists(TablesName::CONFORMANCE);
        Schema::dropIfExists(TablesName::User_Role);
        Schema::dropIfExists(TablesName::Roles);
    }
}
