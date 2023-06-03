<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Tobuli\Entities\TrackerPort;
use Tobuli\Entities\User;

class TrackerConfig extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(User $user = null)
    {
        $this->queue = 'service';
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $cur_ports = TrackerPort::all();
        generateConfig($cur_ports);
    }
}
