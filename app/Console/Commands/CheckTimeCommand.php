<?php

namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;

class CheckTimeCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'check:time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
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
        $this->line(date('Y-m-d H:i:s O'));
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
