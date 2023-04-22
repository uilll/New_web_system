<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsPermission extends Migration
{

    const KEY_NAME = 'reports';
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $mainUserPermissions = settings('main_settings.user_permissions');
        if ($mainUserPermissions && empty($mainUserPermissions[self::KEY_NAME]))
        {
            $mainUserPermissions[self::KEY_NAME] = ['view' => 1, 'edit' => 1, 'remove' => 1];

            settings('main_settings.user_permissions', $mainUserPermissions);
        }


        $userCollection = DB::table("users")
            ->addSelect('users.id')
            ->leftJoin('user_permissions', function($join){
                $join->on('users.id', '=', 'user_permissions.user_id')
                    ->where('user_permissions.name', '=', 'reports');
            })
            ->whereNull('user_permissions.user_id')
            ->whereNull('users.billing_plan_id')
            ->get();

        $usersPermisions = [];
        foreach ($userCollection as $user) {
            $usersPermisions[] = ['user_id' => $user->id, 'name' => self::KEY_NAME, 'view' => 1, 'edit' => 1, 'remove' => 1];
        }

        if ($usersPermisions)
            DB::table('user_permissions')->insert($usersPermisions);



        $billingPlanCollection = DB::table("billing_plans")
            ->addSelect('billing_plans.id')
            ->leftJoin('billing_plan_permissions', function($join){
                $join->on('billing_plans.id', '=', 'billing_plan_permissions.plan_id')
                    ->where('billing_plan_permissions.name', '=', 'reports');
            })
            ->whereNull('billing_plan_permissions.plan_id')
            ->get();

        $billingPlanPermisions = [];
        foreach ($billingPlanCollection as $billingPlan) {
            $billingPlanPermisions[] = ['plan_id' => $billingPlan->id, 'name' => self::KEY_NAME, 'view' => 1, 'edit' => 1, 'remove' => 1];
        }

        if ($billingPlanPermisions)
            DB::table('billing_plan_permissions')->insert($billingPlanPermisions);
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
