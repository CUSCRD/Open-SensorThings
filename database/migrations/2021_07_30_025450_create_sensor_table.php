<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSensorTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::SENSOR, function (Blueprint $table) {
            $table->id('id');
            $table->string('name')->nullable(false);
            $table->string('description',4095)->nullable(true);
            $table->unsignedBigInteger('encodingType')->nullable(false);
            $table->string('metadata',4095)->nullable(true);

            $table->foreign('encodingType')
                ->on(TablesName::ENCODING_TYPE)
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
        DB::table(TablesName::SENSOR)
            ->insert([
                'name'=>'TMP36',
                'description'=>'TMP36 analog temperature sensor',
                'encodingType'=>1,
                'metadata'=>'http://example.org/TMP35_36_37.pdf'
            ]);
        DB::table(TablesName::SENSOR)
            ->insert([
                'name'=>'TMP_HMD',
                'description'=>'Temperature and humidity',
                'encodingType'=>1,
                'metadata'=>''
            ]);
    }
    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE);
        Schema::dropIfExists(TablesName::OBSERVATION);
        Schema::dropIfExists(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE);
        Schema::dropIfExists(TablesName::DATA_STREAM_MEASUREMENT_UNIT);
        Schema::dropIfExists(TablesName::MULTI_DATA_STREAM);
        Schema::dropIfExists(TablesName::SENSOR);
    }
}
