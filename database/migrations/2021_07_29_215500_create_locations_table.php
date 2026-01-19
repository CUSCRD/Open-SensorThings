<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateLocationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('description');
            $table->string('coordinates');
            $table->timestamps();
        });
        DB::table('locations')
            ->insert([
                'name' => 'Vị trí trường Đại học Cần Thơ',
                'description' => 'Khu II, Đ. 3 Tháng 2, Xuân Khánh, Ninh Kiều, Cần Thơ, Việt Nam',
                'coordinates' => '[10.03133564716779, 105.77088992116212]'
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('locations');
    }
}
