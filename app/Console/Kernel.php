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
        \App\Console\Commands\Inspire::class,
        \App\Console\Commands\AutoCleanServerCommand::class,
        \App\Console\Commands\AutoCleanServerFilterCommand::class,
        \App\Console\Commands\BackupMysqlCommand::class,
        \App\Console\Commands\CheckAlertsCommand::class,
        \App\Console\Commands\CheckServerCommand::class,
        \App\Console\Commands\CheckServiceCommand::class,
        \App\Console\Commands\CheckServiceExpireCommand::class,
        \App\Console\Commands\CleanReportLogCommand::class,
        \App\Console\Commands\ReportsDailyCommand::class,
        \App\Console\Commands\SendEventsCommand::class,
        \App\Console\Commands\ReportsCleanCommand::class,
        \App\Console\Commands\GenerateConfigCommand::class,
        \App\Console\Commands\TrackerRestartCommand::class,
        \App\Console\Commands\UpdateServerCommand::class,
        \App\Console\Commands\CleanServerCommand::class,
        \App\Console\Commands\OptimizeServerDBCommand::class,
        \App\Console\Commands\CompressLogsCommand::class,
        \App\Console\Commands\UpdateIconsCommand::class,
        \App\Console\Commands\InsertCommand::class,
        \App\Console\Commands\InsertBulkCommand::class,
        \App\Console\Commands\CleanUnregisteredDeviceLogCommand::class,
        \App\Console\Commands\CheckPositionsCommand::class,
        \App\Console\Commands\CheckTimeCommand::class,
        \App\Console\Commands\ResetDevicesTimezoneCommand::class,
        \App\Console\Commands\CheckStopDurationAlertsCommand::class,
        \App\Console\Commands\CleanUserCommand::class,
        \App\Console\Commands\CleanPositionsCommand::class,
        \App\Console\Commands\CheckMonitoringsCommand::class,
        \App\Console\Commands\CheckPowercutCommand::class,
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
