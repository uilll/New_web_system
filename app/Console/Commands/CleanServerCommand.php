<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\File;

use App\Console\ProcessManager;

class CleanServerCommand extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'server:clean';

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
        $this->processManager = new ProcessManager($this->name, $timeout = 3600, $limit = 1);

        if (!$this->processManager->canProcess())
        {
            echo "Cant process \n";
            return false;
        }

		$date =  $this->argument('date').' 00:00:00';
		$devices = DB::connection('traccar_mysql')->table('devices')->orderBy('id', 'asc')->get();
		$all = count($devices);
		$i = 1;
		foreach ($devices as $device) {
            if (Schema::connection('traccar_mysql')->hasTable('positions_'.$device->id))
                DB::connection('traccar_mysql')->table('positions_'.$device->id)->where('time', '<', $date)->delete();

            if (Schema::connection('engine_hours_mysql')->hasTable('engine_hours_'.$device->id))
                DB::connection('engine_hours_mysql')->table('engine_hours_'.$device->id)->where('time', '<', $date)->delete();

			$this->line("CLEAN TABLES ({$i}/{$all})\n");
			$i++;
		}

		$this->line("Job done[OK]\n");
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			array('date', InputArgument::REQUIRED, 'The date')
		);
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
