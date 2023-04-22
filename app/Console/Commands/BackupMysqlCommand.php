<?php namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;

use App\Console\ProcessManager;
use Tobuli\Helpers\Backup;

class BackupMysqlCommand extends Command {
	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'backup:mysql';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Command description.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        /* $this->processManager = new ProcessManager($this->name, $timeout = 72000, $limit = 1);

        if ( ! $this->processManager->canProcess())
        {
            echo "Cant process \n";
            return false;
        }

        $backup = new Backup();

        $backup->auto();

		$this->line("Job done\n"); */
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
