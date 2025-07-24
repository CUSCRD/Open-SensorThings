<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
// một datastream gom nhóm 1 tập hợp các đo đạc Observations một ObservedProperty và được tạo bởi cùng Sensor
class CreateMultiDataStreamTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::MULTI_DATA_STREAM, function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('thingId')->nullable(false);
            $table->unsignedBigInteger('sensorId')->nullable(false);
            $table->string('name')->nullable(false);
            $table->string('description')->nullable(false);
            $table->unsignedBigInteger('observationType')->default(1)->nullable(false);

            $table->foreign('observationType')
                ->references('id')
                ->on(TablesName::OBSERVATION_TYPE)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('thingId')
                ->references('id')
                ->on(TablesName::THING)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('sensorId')
                ->references('id')
                ->on(TablesName::SENSOR)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table(TablesName::MULTI_DATA_STREAM)
            ->insert([
                'thingId' => 1,
                'sensorId' => 1,
                'name' => 'Light sensor',
                'description' => 'This is a datastream measuring the light of env.',
                'observationType' => 1
            ]);
        DB::table(TablesName::MULTI_DATA_STREAM)
            ->insert([
                'thingId' => 1,
                'sensorId' => 2,
                'name' => 'CO2 Sensor',
                'description' => 'This is a datastream measuring the air temperature in an oven.',
                'observationType' => 1
            ]);
        DB::table(TablesName::MULTI_DATA_STREAM)
            ->insert([
                'thingId' => 1,
                'sensorId' => 3,
                'name' => 'Temperature Sensor',
                'description' => 'This is a datastream measuring the air temperature in an oven.',
                'observationType' => 1
            ]);
        DB::table(TablesName::MULTI_DATA_STREAM)
            ->insert([
                'thingId' => 1,
                'sensorId' => 4,
                'name' => 'Humidity sensor',
                'description' => 'This is a datastream measuring the air temperature in an oven.',
                'observationType' => 1
            ]);

        DB::table(TablesName::MULTI_DATA_STREAM)
        ->insert([
            'thingId' => 2,
            'sensorId' => 1,
            'name' => 'Light DS',
            'description' => 'Light sensor in almond tree from CUSC',
            'observationType' => 1
        ]);

        DB::table(TablesName::MULTI_DATA_STREAM)
            ->insert([
                'thingId' => 2,
                'sensorId' => 5,
                'name' => 'Soil DS',
                'description' => 'Soild= sensor in almond tree from CUSC',
                'observationType' => 1
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
    }
}
