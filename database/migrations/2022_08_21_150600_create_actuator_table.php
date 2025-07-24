<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constant\TablesName;
use Illuminate\Support\Facades\DB;

class CreateActuatorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('actuator', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('description');
            $table->unsignedBigInteger('encodingType');
            $table->timestamps();

            $table->foreign('encodingType')
                ->references('id')
                ->on(TablesName::ENCODING_TYPE)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table(TablesName::ACTUATOR)
            ->insert([
                'name' => 'Actuator test',
                'description' => 'Test',
                'encodingType' => 1
            ]);
        DB::table(TablesName::ACTUATOR)
            ->insert([
                'name' => 'LED',
                'description' => 'Linkit Smart 7688 Duo Board that has an LED which can be tasked as on/off.',
                'encodingType' => 1
            ]);
        DB::table(TablesName::ACTUATOR)
            ->insert([
                'name' => 'BUMP',
                'description' => 'Turn on or turn off a water BUMP, Which is installed on a tree.',
                'encodingType' => 1
            ]);
        DB::table(TablesName::ACTUATOR)
            ->insert([
                'id'=>7,
                'name' => 'Valve',
                'description' => 'Turn on or turn off a water value, Which is installed on a tree',
                'encodingType' => 1
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_actuator');
    }
}
