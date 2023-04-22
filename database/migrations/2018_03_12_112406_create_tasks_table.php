<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('tasks')) { return; }

        Schema::create('tasks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('device_id');
            $table->integer('user_id');
            $table->text('title')->nullable();
            $table->longText('comment')->nullable();
            $table->tinyInteger('priority')->default(2);
            $table->integer('status')->default(1);
            $table->longText('pickup_address');
            $table->double('pickup_address_lat');
            $table->double('pickup_address_lng');
            $table->timestamp('pickup_time_from');
            $table->timestamp('pickup_time_to');
            $table->longText('delivery_address');
            $table->double('delivery_address_lat');
            $table->double('delivery_address_lng');
            $table->timestamp('delivery_time_from');
            $table->timestamp('delivery_time_to');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('tasks');

    }
}
