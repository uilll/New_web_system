<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'App\Console\Commands\Inspire',
        'App\Console\Commands\AutoCleanServerCommand',
        'App\Console\Commands\AutoCleanServerFilterCommand',
        'App\Console\Commands\BackupMysqlCommand',
        'App\Console\Commands\CheckAlertsCommand',
        'App\Console\Commands\CheckServerCommand',
        'App\Console\Commands\CheckServiceCommand',
        'App\Console\Commands\CheckServiceExpireCommand',
        'App\Console\Commands\CleanReportLogCommand',
        'App\Console\Commands\ReportsDailyCommand',
        'App\Console\Commands\SendEventsCommand',
        'App\Console\Commands\ReportsCleanCommand',
        'App\Console\Commands\GenerateConfigCommand',
        'App\Console\Commands\TrackerRestartCommand',
        'App\Console\Commands\UpdateServerCommand',
        'App\Console\Commands\CleanServerCommand',
        'App\Console\Commands\OptimizeServerDBCommand',
        'App\Console\Commands\CompressLogsCommand',
        'App\Console\Commands\UpdateIconsCommand',
        'App\Console\Commands\InsertCommand',
        'App\Console\Commands\InsertBulkCommand',
        'App\Console\Commands\CleanUnregisteredDeviceLogCommand',
        'App\Console\Commands\CheckPositionsCommand',
        'App\Console\Commands\CheckTimeCommand',
        'App\Console\Commands\ResetDevicesTimezoneCommand',
        'App\Console\Commands\CheckStopDurationAlertsCommand',
        'App\Console\Commands\CleanUserCommand',
        'App\Console\Commands\CleanPositionsCommand',
        'App\Console\Commands\CheckMonitoringsCommand',
        'App\Console\Commands\CheckPowercutCommand',
    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //$schedule->command('inspire')
        //		 ->hourly();
        /*$schedule->command('clean:positions')
                 ->hourly();
        $schedule->command('server:dboptimize')
                ->dailyAt('3:00');
        $schedule->command('check:monitorings')
                ->everyFiveMinutes();
        $schedule->command('check:powercut')
                ->everyMinute();//FiveMinutes();*/
        //everyThirtyMinutes(); //->hourly(); ->everyThirtyMinutes()*/everyMinute()
        //$schedule->command('server:dboptimize') 				 ->dailyAt('3:00');
        //$schedule->command('check:monitorings')->hourly();
    }
}
