<?php

use App\Constant\TablesName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateObservedPropertyTable extends Migration
{
    public function up()
    {
        Schema::create(TablesName::OBSERVED_PROPERTY, function (Blueprint $table) {
            $table->id('id');
            $table->string('name');
            //definition uri
            $table->string('definition',4095);
            $table->string('description',4095)->default(null)->nullable(true);
        });

        DB::table(TablesName::OBSERVED_PROPERTY)
            ->insert([
                "description"=>
                    "The dewpoint temperature is the temperature to which the air must be
                      cooled, at constant pressure, for dew to form. As the grass and other objects
                      near the ground cool to the dewpoint, some of the water vapor in the
                      atmosphere condenses into liquid water on the objects.",
                "name"=>"DewPoint Temperature",
                "definition"=>"http://dbpedia.org/page/Dew_point"
            ]);
        DB::table(TablesName::OBSERVED_PROPERTY)
            ->insert([
                "description"=>
                    "Relative humidity (abbreviated RH) is the ratio of the partial pressure of water vapor to the equilibrium vapor pressure of water at the same temperature.",
                "name"=>"Relative Humidity",
                "definition"=>"https://dbpedia.org/page/Humidity"
            ]);
    }
    public function down()
    {
        Schema::dropIfExists(TablesName::DATA_STREAM_OBSERVED_PROPERTY);
        Schema::dropIfExists(TablesName::OBSERVED_PROPERTY);
    }
}
