<?php

use App\Constant\TablesName;
use App\Models\User\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TablesName::Users, function (Blueprint $table) {
            $table->id();
            $table->string('username')->unique();
            $table->string('password');
//            $table->bigInteger('role');
            $table->rememberToken();
            $table->timestamps();
        });

        $user=new User();
        $user->fill(['username'=>'admin','password'=>Hash::make("admin")]);
        $user->save();

        $user=new User();
        $user->fill(['username'=>'post','password'=>Hash::make("post")]);
        $user->save();

        $user=new User();
        $user->fill(['username'=>'patch','password'=>Hash::make("patch")]);
        $user->save();

        $user=new User();
        $user->fill(['username'=>'delete','password'=>Hash::make("delete")]);
        $user->save();

        $user=new User();
        $user->fill(['username'=>'get','password'=>Hash::make("get")]);
        $user->save();

        $user=new User();
        $user->fill(['username'=>'batch','password'=>Hash::make("batch")]);
        $user->save();

        $user=new User();
        $user->fill(['username'=>'sensor','password'=>Hash::make("sensor")]);
        $user->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TablesName::User_Role);
        Schema::dropIfExists(TablesName::Users);
    }
}
