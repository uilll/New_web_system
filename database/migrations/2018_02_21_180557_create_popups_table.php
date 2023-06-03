<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('popups')) {
            return;
        }

        Schema::create('popups', function (Blueprint $table) {
            $table->increments('id');
            $table->text('title')->nullable();
            $table->longText('content')->nullable();
            $table->text('position')->nullable();
            $table->integer('show_every_days')->nullable();
            $table->tinyInteger('active')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('popups');
    }
}
