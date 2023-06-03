<?php

namespace App\Console\Commands;

ini_set('memory_limit', '-1');
set_time_limit(0);

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class UpdateServerCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'server:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates server database and configuration to the newest version.';

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
        try {
            $current = base_path('resources/lang/');
            $original = base_path('resources/original_lang/');
            if (! File::exists($original)) {
                File::makeDirectory($original, $mode = 0777, true, true);
            }
            File::copyDirectory($current, $original);

            $langs = glob(storage_path('langs/*'));
            foreach ($langs as $lang) {
                $arr = explode('/', $lang);
                $name = end($arr);
                $files = glob($lang.'/*');
                foreach ($files as $file) {
                    $sarr = explode('/', $file);
                    $sname = end($sarr);
                    $translations = include base_path("resources/original_lang/{$name}/".$sname);
                    $trans = include $file;
                    file_put_contents($file, parseTranslations($translations, $trans));
                    File::copy($file, base_path("resources/lang/{$name}/{$sname}"));
                    $this->line($file.' --> '.base_path("resources/lang/{$name}/{$sname}"));
                }
            }

            $this->line('Ok');
        } catch (\Exception $e) {
        }
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
