<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateThingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TablesName::THING, function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->string('description');
            $table->string('properties')->default(null)->nullable(true);
            $table->bigInteger('id_user');
            $table->string('avt_image')->default(null)->nullable(true);
            $table->bigInteger('id_location');
            $table->foreign('id_user')->references('id')->on(TablesName::Users)->cascadeOnUpdate()->cascadeOnDelete();
            $table->foreign('id_location')->references('id')->on(TablesName::LOCATION)->cascadeOnUpdate()->cascadeOnDelete();
        });
        // DB::table(TablesName::THING)
        //     ->insert([
        //         'name' => 'Oven',
        //         'id_user' => 1,
        //         'avt_image' => '',
        //         'description' => 'This is an oven',
        //         'properties' => json_encode(["owner" => "Noah Liang", "color" => "Black"]),
        //         'id_location' => 1
        //     ]);
        DB::table(TablesName::THING)
            ->insert([
                'name' => 'Campus garden CUSC',
                'id_user' => 1,
                'avt_image' => 'https://fms-laravel-images.s3.ap-southeast-1.amazonaws.com/images/uJP1tka58h1FEbs0HFRSRFYIVIGrItS5BsQYbg3d.jpg',
                'description' => 'This is an campus from CUSC in CTU',
                'properties' => '{"owner":"CUSC","address":"1 Ly Tu Trong","province":"Can Tho","district":"Ninh Kieu","ward":"Xuan Khanh"}',
                'id_location' => 1
            ]);
        DB::table(TablesName::THING)
            ->insert([
                'name' => 'Almond tree CUSC',
                'id_user' => 1,
                'avt_image' => 'https://fms-laravel-images.s3.ap-southeast-1.amazonaws.com/images/Ag21gNjHy5GyPHu7JlZK8GNAchkC9qYCHQYdqMRG.jpg',
                'description' => 'This is an Almond tree from CUSC in CTU',
                'properties' => '{"owner":"CUSC","address":"1 Ly Tu Trong","province":"Can Tho","district":"Ninh Kieu","ward":"Xuan Khanh"}',
                'id_location' => 1
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE);
        Schema::dropIfExists(TablesName::DATA_STREAM_OBSERVED_PROPERTY);
        Schema::dropIfExists(TablesName::DATA_STREAM_MEASUREMENT_UNIT);
        Schema::dropIfExists(TablesName::MULTI_DATA_STREAM);
        Schema::dropIfExists(TablesName::THING);
    }
}
