<?php

use App\Constant\OGC\SensorEncodingType;
use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateSensorEncodingTypeTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::ENCODING_TYPE, function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            $table->string('value');
        });
        foreach (SensorEncodingType::getConstants() as $item){
            DB::table(TablesName::ENCODING_TYPE)
                ->insert([
                    'name'=>$item['encodingType'],
                    'value'=>$item['value']
                ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists(TablesName::SENSOR);
        Schema::dropIfExists(TablesName::ENCODING_TYPE);
    }
}
