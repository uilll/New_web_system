<?php

use Illuminate\Database\Migrations\Migration;

class AlterUserTableSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('users', 'settings')) {
            DB::statement('ALTER TABLE  `users` ADD  `settings` TEXT NULL ;');
        }
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
