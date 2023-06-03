<?php

namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);

use App\Console\ProcessManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class OptimizeServerDBCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'server:dboptimize';

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

        if (! $this->processManager->canProcess()) {
            echo "Cant process \n";

            return false;
        }

        $connections = [
            'mysql' => 'gpswox_web',
            'traccar_mysql' => 'gpswox_traccar',
        ];

        foreach ($connections as $key => $name) {
            $tables = DB::connection($key)->select('SHOW TABLES');
            $all = count($tables);
            $i = 1;
            foreach ($tables as $table) {
                $dbname = 'Tables_in_'.$name;
                $tableName = $table->{$dbname};
                DB::connection($key)->statement("OPTIMIZE TABLE {$tableName};");
                $this->line("OPTIMIZE TABLE {$name} ({$i}/{$all})\n");
                $i++;
            }
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
        return [];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [];
    }
}
