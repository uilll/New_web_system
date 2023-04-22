<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Tobuli\Entities\EmailTemplate;


class AddAccountCreatedTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('email_templates'))
        {
            $template = new EmailTemplate([
                'title' => 'Account created',
                'note' => 'Hello, <br><br> Your account was created. <br><br> Login information: <br> Email: [email] <br> Password: [password]'
            ]);
            $template->name = 'account_created';
            $template->save();
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
