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
            $table->string('description', 4095)->nullable(true);
            $table->unsignedBigInteger('encodingType')->nullable(false);
            $table->string('metadata', 4095)->nullable(true);

            $table->foreign('encodingType')
                ->on(TablesName::ENCODING_TYPE)
                ->references('id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
        DB::table(TablesName::SENSOR)
            ->insert([
                'name' => 'BH1750 Light sensor',
                'description' => '  Name: Light sensor
                                    Unit: Lux (1 lx = 1 lm/m²)
                                    Power supply: 3~5VDC
                                    Communication standard: I2C
                                    Measuring range: 1 - 65535 lux
                                    Accuracy: +/- 20%reading value',
                'encodingType' => 1,
                'metadata' => 'http://example.org/TMP35_36_37.pdf'
            ]);
        DB::table(TablesName::SENSOR)
            ->insert([
                'name' => 'MH-Z19 CO2 sensor',
                'description' => '  Name: Co2 sensor
                                    Unit: ppm ( 1ppm = 1 mg/kg)
                                    Power supply: 4.5~5.5VDC
                                    Communication standard: UART (TTL-3V3)
                                    Measuring range: 0～5000 ppm
                                    Accuracy: ± (50+3%reading value)',
                'encodingType' => 1,
                'metadata' => ''
            ]);
        DB::table(TablesName::SENSOR)
            ->insert([
                'name' => 'SHT30 Temperature sensor',
                'description' => '  Name: Temperture sensor
                                    Unit: Degree
                                    Power supply: 4.5~5.5VDC
                                    Communication standard: I2C
                                    Measuring range: 0 - 65
                                    Accuracy: ± 0.2',
                'encodingType' => 1,
                'metadata' => ''
            ]);
        DB::table(TablesName::SENSOR)
            ->insert([
                'name' => 'SHT30 Huminity sensor',
                'description' => '  Name: Huminity sensor
                                    Unit: Relative humidity (%RH)
                                    Power supply: 4.5 to 5.5VDC
                                    Communication standard: I2C
                                    Measuring range: 0 to 100
                                    Accuracy: ± 3',
                'encodingType' => 1,
                'metadata' => ''
            ]);
        DB::table(TablesName::SENSOR)
            ->insert([
                'name' => 'Soil Moisture Sensor',
                'description' => '  Name: Soil Moisture Sensor Corrosion Resistance Probe
                                    Unit: Relative humidity (%RH)
                                    Power supply: 3.3~12VDC
                                    Communication standard: Analog
                                    Measuring range: 0 to 100
                                    Accuracy: ± 5',
                'encodingType' => 1,
                'metadata' => ''
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
