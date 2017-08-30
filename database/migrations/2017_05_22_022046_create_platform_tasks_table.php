<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePlatformTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('platform_tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('parent_id');
            $table->string('title');
            $table->longText('payload');
            $table->longText('info')->nullable();
            $table->longText('output')->nullable();
            $table->tinyInteger('status')->default(0);
            $table->nullableTimestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('platform_tasks');
    }
}
