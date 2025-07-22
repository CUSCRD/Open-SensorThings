<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constant\TablesName;

class CreateDataUnitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_unit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('dataStreamId');
            $table->string('definition');
            $table->timestamps();

            $table->foreign('dataStreamId')
                ->references('id')
                ->on(TablesName::MULTI_DATA_STREAM)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('unit_id')
                ->references('id')
                ->on(TablesName::MEASUREMENT_UNIT)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('data_unit');
    }
}
