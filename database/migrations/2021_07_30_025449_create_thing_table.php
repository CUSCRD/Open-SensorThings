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
        });
        DB::table(TablesName::THING)
            ->insert([
                'name'=>'Oven',
                'description'=>'This is an oven',
                'properties'=>json_encode(["owner"=> "Noah Liang","color"=>"Black"])
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
