<?php

use App\Constant\OGC\MeasurementUnitData;
use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateMeasurementUnitTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::MEASUREMENT_UNIT, function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->string('symbol');
            $table->string('definition');
        });
        $const=MeasurementUnitData::getConstants();
        foreach ($const as $item){
            DB::table(TablesName::MEASUREMENT_UNIT)
                ->insert([
                    'name'=>$item['name'],
                    'symbol'=>$item['symbol'],
                    'definition'=>$item['definition']
                ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_MEASUREMENT_UNIT);
        Schema::dropIfExists(TablesName::MEASUREMENT_UNIT);
    }
}
