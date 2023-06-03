<?php

use Illuminate\Database\Migrations\Migration;

class DevicesSensorsUnitOfMeasurementAlter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `device_sensors` CHANGE `unit_of_measurement` `unit_of_measurement` VARCHAR(32);');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
