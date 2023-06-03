<?php

use Illuminate\Database\Migrations\Migration;

class AddUsersMetersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('users', 'unit_of_altitude')) {
            Schema::table('users', function ($table) {
                $table->char('unit_of_altitude', 2)->default('mt');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
