<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constant\TablesName;
use Illuminate\Support\Facades\DB;

class CreateTaskTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('task', function (Blueprint $table) {
            $table->id();
            $table->integer('taskingParameters');
            $table->timestamps();

            $table->foreign('id')
                ->references('id')
                ->on(TablesName::Tasking_capability)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        DB::table(TablesName::TASK)
            ->insert([
                'id' => 11,
                'taskingParameters' => -1
            ]);
            DB::table(TablesName::TASK)
            ->insert([
                'id' => 19,
                'taskingParameters' => -1
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('_task');
    }
}