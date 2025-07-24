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
            $table->string('displayname');
            $table->string('phone')->unique();
            $table->string('avatar')->default(null)->nullable(true);;
            $table->timestamp('phone_verified_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        $user = new User();
        $user->fill([
            'username' => 'admin',
            'password' => Hash::make("admin"),
            'displayname' => 'Administrator',
            'phone' => '0000000001'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'post',
            'password' => Hash::make("post"),
            'displayname' => 'Post',
            'phone' => '0000000002'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'patch',
            'password' => Hash::make("patch"),
            'displayname' => 'Patch',
            'phone' => '0000000003'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'delete',
            'password' => Hash::make("delete"),
            'displayname' => 'Delete',
            'phone' => '0000000004'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'get',
            'password' => Hash::make("get"),
            'displayname' => 'Get',
            'phone' => '0000000005'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'batch',
            'password' => Hash::make("batch"),
            'displayname' => 'Batch',
            'phone' => '0000000006'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'sensor',
            'password' => Hash::make("sensor"),
            'displayname' => 'Sensor',
            'phone' => '0000000007'
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'quykhang831',
            'password' => Hash::make("quykhang831"),
            'displayname' => 'Khang',
            'phone' => '0944093777',
            'avatar' => null
        ]);
        $user->save();

        $user = new User();
        $user->fill([
            'username' => 'tuhuuduc',
            'password' => Hash::make("tuhuuduc"),
            'displayname' => 'Đức',
            'phone' => '0965735649',
            'avatar' => 'https://fms-laravel-images.s3.ap-southeast-1.amazonaws.com/images/qwcgtlNjA6rpoqGhUFXNtjeD0IQRt4akWRfKsbeF.jpg',
        ]);
        $user->save();

        // $user = new User();
        // $user->fill(['username' => 'patch', 'password' => Hash::make("patch")]);
        // $user->save();

        // $user = new User();
        // $user->fill(['username' => 'delete', 'password' => Hash::make("delete")]);
        // $user->save();

        // $user = new User();
        // $user->fill(['username' => 'get', 'password' => Hash::make("get")]);
        // $user->save();

        // $user = new User();
        // $user->fill(['username' => 'batch', 'password' => Hash::make("batch")]);
        // $user->save();

        // $user = new User();
        // $user->fill(['username' => 'sensor', 'password' => Hash::make("sensor")]);
        // $user->save();
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
