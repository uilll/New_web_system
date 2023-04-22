<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

use Tobuli\Entities\BillingPlan;
use Tobuli\Entities\Device;
use Tobuli\Entities\User;

class CleanUserCommand extends Command {
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'user:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $this->line("Free users last login more than two months ago.");

        $freePlan = BillingPlan::where('title', 'Free')->first();

        if ( ! $freePlan) {
            $this->line("Can't find free plan");
            die();
        }
        $users = User
            ::where('loged_at', '<', Carbon::now()->subMonths(2))
            ->where('billing_plan_id', $freePlan->id)
            ->get();

        foreach ($users as $user)
        {
            $this->userFullDelete($user);
        }

        $this->line("Free users without object.");

        $users = User
            ::leftJoin('devices', 'users.id', '=', 'devices.user_id')
            ->where('loged_at', '<', Carbon::now()->subMonths(1))
            ->where('billing_plan_id', $freePlan->id)
            ->whereNull('devices.id')
            ->get();

        foreach ($users as $user)
        {
            $this->userFullDelete($user);
        }

        $this->line("Expired users where last login before 3 months and more");

        $users = User
            ::where('subscription_expiration', '!=', '0000-00-00 00:00:00')
            ->where('subscription_expiration', '<', Carbon::now())
            ->where('loged_at', '<', Carbon::now()->subMonths(3))
            ->get();

        foreach ($users as $user)
        {
            $this->userFullDelete($user);
        }

        $this->line("Never connected devices");

        $devices = Device
            ::where('devices.updated_at', '<', Carbon::now()->subMonths(1))
            ->select('devices.*')
            ->join('gpswox_traccar.devices as traccar_devices', 'devices.traccar_device_id', '=', 'traccar_devices.id')
            ->whereNull('traccar_devices.server_time')
            ->get();

        foreach ($devices as $device)
        {
            $this->deviceDelete($device);
        }

        $this->line("Job done[OK]\n");
    }

    private function userFullDelete(User $user)
    {
        $this->line($user->id.' '.$user->email);

        $devices = $user->devices;

        foreach ($devices as $device)
        {
            $this->deviceDelete($device);
        }

        DB::table('user_drivers')->where('user_id', $user->id)->delete();

        $user->delete();
    }

    private function deviceDelete(Device $device)
    {
        DB::table('events')->where('device_id', $device->id)->delete();
        DB::table('device_sensors')->where('device_id', $device->id)->delete();
        DB::table('device_services')->where('device_id', $device->id)->delete();

        DB::connection('traccar_mysql')->table('positions_'.$device->traccar_device_id)->truncate();

        if (Schema::connection('sensors_mysql')->hasTable('sensors_'.$device->traccar_device_id))
            DB::connection('sensors_mysql')->table('sensors_'.$device->traccar_device_id)->truncate();

        if (Schema::connection('engine_hours_mysql')->hasTable('engine_hours_'.$device->traccar_device_id))
            DB::connection('engine_hours_mysql')->table('engine_hours_'.$device->traccar_device_id)->truncate();

        Schema::connection('traccar_mysql')->dropIfExists('positions_'.$device->traccar_device_id);
        Schema::connection('sensors_mysql')->dropIfExists('sensors_'.$device->traccar_device_id);
        Schema::connection('engine_hours_mysql')->dropIfExists('positions_'.$device->traccar_device_id);

        DB::connection('traccar_mysql')->table('devices')->where('id', '=', $device->traccar_device_id)->delete();

        $device->delete();
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }
}
