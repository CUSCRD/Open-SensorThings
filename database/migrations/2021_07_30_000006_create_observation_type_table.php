<?php

use App\Constant\OGC\ObservationTypeData;
use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateObservationTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(TablesName::OBSERVATION_TYPE, function (Blueprint $table) {
            $table->id('id');
            $table->string('code');
            $table->string('value');
            $table->string('result');
        });
        $arr= ObservationTypeData::getConstants();
        foreach ($arr as $item){
            DB::table(TablesName::OBSERVATION_TYPE)
                ->insert([
                    'code'=>$item['code'],
                    'value'=>$item['codeValue'],
                    'result'=>$item['result']
                ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_MULTI_OBSERVATION_TYPE);
        Schema::dropIfExists(TablesName::OBSERVATION_TYPE);
    }
}
