<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSensorGroupSensorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('sensor_group_sensors')) {
            return;
        }

        Schema::create('sensor_group_sensors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned()->index();
            $table->string('name');
            $table->string('type', 25)->index();
            $table->string('tag_name')->nullable()->index();
            $table->boolean('add_to_history')->default('0')->index();
            $table->string('on_value')->nullable();
            $table->string('off_value')->nullable();
            $table->string('shown_value_by', 30)->nullable();
            $table->string('fuel_tank_name')->nullable();
            $table->string('full_tank', 10)->nullable();
            $table->string('full_tank_value', 10)->nullable();
            $table->string('min_value', 10)->nullable();
            $table->string('max_value', 10)->nullable();
            $table->string('formula')->nullable();
            $table->string('odometer_value_by')->nullable();
            $table->float('odometer_value')->unsigned()->nullable();
            $table->string('odometer_value_unit')->default('km');
            $table->string('temperature_max', 10)->nullable();
            $table->string('temperature_max_value', 10)->nullable();
            $table->string('temperature_min', 10)->nullable();
            $table->string('temperature_min_value', 10)->nullable();
            $table->string('value')->default('-')->nullable();
            $table->integer('value_formula')->default('0');
            $table->boolean('show_in_popup')->default('0')->index();
            $table->string('unit_of_measurement', 3)->nullable();
            $table->string('on_tag_value')->nullable();
            $table->string('off_tag_value')->nullable();
            $table->tinyInteger('on_type')->nullable();
            $table->tinyInteger('off_type')->nullable();
            $table->mediumText('calibrations')->nullable();

            $table->foreign('group_id')->references('id')->on('sensor_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('sensor_group_sensors');
    }
}
