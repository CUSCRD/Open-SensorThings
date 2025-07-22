<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constant\TablesName;
use Illuminate\Support\Facades\DB;

class CreateTaskingCapabilityTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tasking_capability', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('actuator_id');
            $table->unsignedBigInteger('thing_id');
            $table->string('name')->nullable(false);
            $table->string('description');
            $table->string('properties');
            $table->timestamps();

            $table->foreign('actuator_id')
                ->references('id')
                ->on(TablesName::Actuator)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('thing_id')
                ->references('id')
                ->on(TablesName::THING)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table(TablesName::TASKINGCAPABILITY)
            ->insert([
                'id' => 11,
                'actuator_id' => 2,
                'thing_id ' => 2,
                'taskingParameters' => '{
                                            "type": "DataRecord",
                                            "field": [
                                                {
                                                    "name": "status",
                                                    "type": "Category",
                                                    "label": "On/Off status",
                                                    "constraint": {
                                                        "type": "AllowedTokens",
                                                        "value": [
                                                            "on",
                                                            "off"
                                                        ]
                                                    },
                                                    "description": "Specifies turning the light On or Off"
                                                },
                                                {
                                                    "name": "color",
                                                    "type": "Text",
                                                    "label": "Light Color",
                                                    "constraint": {
                                                        "type": "AllowedTokens",
                                                        "pattern": "^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$"
                                                    },
                                                    "description": "Specifies the light color in RGB HEX format. Example: #FF11A0"
                                                }
                                            ]
                                        }'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_tasking_capability');
    }
}
