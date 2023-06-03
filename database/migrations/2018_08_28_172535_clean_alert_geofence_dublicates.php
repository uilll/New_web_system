<?php

use Illuminate\Database\Migrations\Migration;

class CleanAlertGeofenceDublicates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $items = DB::table('alert_geofence')
            ->select('*', DB::raw('COUNT(*) as total'))
            ->groupBy(['alert_id', 'geofence_id'])
            ->having('total', '>', 1)
            ->get();

        foreach ($items as $item) {
            DB::table('alert_geofence')->where('alert_id', $item->alert_id)->where('geofence_id', $item->geofence_id)->delete();
            DB::table('alert_geofence')->insert(['alert_id' => $item->alert_id, 'geofence_id' => $item->geofence_id]);
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
