<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDataStreamMeasurementUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TablesName::DATA_STREAM_MEASUREMENT_UNIT, function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('unitId')->nullable(false);
            $table->unsignedBigInteger('dataStreamId')->nullable(false);

            $table->foreign('unitId')
                ->references('id')
                ->on(TablesName::MEASUREMENT_UNIT)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('dataStreamId')
                ->references('id')
                ->on(TablesName::MULTI_DATA_STREAM)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)
            ->insert([
                'unitId'=>1,
                'dataStreamId'=>1
            ]);
        DB::table(TablesName::DATA_STREAM_MEASUREMENT_UNIT)
            ->insert([
                'unitId'=>2,
                'dataStreamId'=>1
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_MEASUREMENT_UNIT);
    }
}
