<?php

use Illuminate\Database\Migrations\Migration;

class DevicesSensorsTableAlter extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE `device_sensors` CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;');
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
