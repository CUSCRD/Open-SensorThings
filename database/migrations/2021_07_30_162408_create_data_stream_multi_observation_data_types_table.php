<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDataStreamMultiObservationDataTypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE, function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('dataStreamId');
            $table->unsignedBigInteger('observedType');

            $table->foreign('dataStreamId')
                ->references('id')
                ->on(TablesName::MULTI_DATA_STREAM)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('observedType')
                ->references('id')
                ->on(TablesName::OBSERVATION_TYPE)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
        DB::table(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE)
            ->insert([
                'dataStreamId'=>1,
                'observedType'=>4
            ]);
        DB::table(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE)
        ->insert([
            'dataStreamId'=>1,
            'observedType'=>4
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
    }
}
