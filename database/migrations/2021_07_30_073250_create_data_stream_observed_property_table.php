<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateDataStreamObservedPropertyTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::DATA_STREAM_OBSERVED_PROPERTY, function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('observedPropertyId');
            $table->unsignedBigInteger('dataStreamId');


            $table->foreign('dataStreamId')
                ->references('id')
                ->on(TablesName::MULTI_DATA_STREAM)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreign('observedPropertyId')
                ->references('id')
                ->on(TablesName::OBSERVED_PROPERTY)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)
            ->insert([
                'observedPropertyId'=>1,
                'dataStreamId'=>1
            ]);
        DB::table(TablesName::DATA_STREAM_OBSERVED_PROPERTY)
            ->insert([
                'observedPropertyId'=>2,
                'dataStreamId'=>1
            ]);
    }
    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_OBSERVED_PROPERTY);
    }
}
