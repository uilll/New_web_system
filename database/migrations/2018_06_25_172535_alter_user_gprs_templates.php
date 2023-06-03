<?php

use Illuminate\Database\Migrations\Migration;

class AlterUserGprsTemplates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('user_gprs_templates', 'protocol')) {
            return;
        }

        Schema::table('user_gprs_templates', function ($table) {
            $table->string('protocol', 20)->after('message')->nullable();
        });
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
