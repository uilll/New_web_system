<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTasksPermission extends Migration
{

    const KEY_NAME = 'tasks';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $userCollection = DB::table("users")->addSelect('users.id')->get();

        foreach ($userCollection as $user) {
            DB::table('user_permissions')->insert(
                ['user_id' => $user->id, 'name' => self::KEY_NAME, 'view' => 1, 'edit' => 1, 'remove' => 1]
            );
        }

        $billingPlanCollection = DB::table("billing_plans")->addSelect('billing_plans.id')->get();

        foreach ($billingPlanCollection as $billingPlan) {
            DB::table('billing_plan_permissions')->insert(
                ['plan_id'  => $billingPlan->id, 'name' => self::KEY_NAME, 'view' => 1, 'edit' => 1, 'remove' => 1]
            );
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
