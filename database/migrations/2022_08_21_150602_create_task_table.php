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
        // Khi tạo mới DB 
        Schema::create('task', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('taskingCapabilityId');
            $table->json('taskingParameters');
            $table->enum('xStatus', ['new', 'processed', 'completed'])->default('new');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('taskingCapabilityId')
                ->references('id')
                ->on(TablesName::Tasking_capability)
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });

        // Dữ liệu mẫu (seed)
        DB::table(TablesName::TASK)->insert([
            [
                'id' => 1,
                'taskingCapabilityId' => 1,
                'taskingParameters' => json_encode(['target' => 'fan', 'value' => 'on']),
                'xStatus' => 'new',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'taskingCapabilityId' => 2,
                'taskingParameters' => json_encode(['target' => 'light', 'value' => 'off']),
                'xStatus' => 'processed',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('task');
    }
}
