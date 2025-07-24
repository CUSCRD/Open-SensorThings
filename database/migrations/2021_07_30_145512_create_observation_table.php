<?php

use App\Constant\TablesName;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateObservationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TablesName::OBSERVATION, function (Blueprint $table) {
            $table->id('id');
            $table->unsignedBigInteger('dataStreamId')->nullable(false);
            //JSON
            $table->longText('result');
            $table->dateTime('resultTime')->nullable(true)->default(null);
            //The time period during which the result may be used.
            $table->dateTime('validTime')->nullable(true);


            $table->foreign('dataStreamId')
                ->references('id')
                ->on(TablesName::MULTI_DATA_STREAM)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

        });

        DB::table(TablesName::OBSERVATION)
            ->insert([
                'dataStreamId'=>1,
                "result"=> json_encode([25,65]),
                "resultTime"=> Carbon::now(),
                "validTime"=>Carbon::now()->addYear()
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TablesName::OBSERVATION);
    }
}
