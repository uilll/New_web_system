<?php namespace App\Console\Commands;
ini_set('memory_limit', '-1');
set_time_limit(0);

use Auth;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Tobuli\Entities\EmailTemplate;
use Tobuli\Entities\SmsTemplate;
use Tobuli\Entities\EventQueue;

use Bugsnag\BugsnagLaravel\BugsnagFacade as Bugsnag;
use App\Console\ProcessManager;
use Tobuli\Protocols\Manager;


class SendEventsCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'events:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check event queue(send notifications and clear).';

    private $idsToDelete = [];

    private $smsTemplate;

    private $emailTemplate;

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
        $this->smsTemplate = SmsTemplate::where('name', 'event')->first();
        $this->emailTemplate = EmailTemplate::where('name', 'event')->first();

        $this->processManager = new ProcessManager($this->name, $timeout = 120, $limit = 2);

        if ( ! $this->processManager->canProcess()) {
            echo "Can't process \n";
            return false;
        }

        DB::disableQueryLog();

        while ($this->processManager->canProcess()) {
            $items = EventQueue::with(['user', 'device', 'device.users'])->orderBy('id', 'asc')->take(100)->get();

            foreach ($items as $item) {
                if ( ! $this->processManager->lock($item->id))
                    continue;

                if (! ($item->user && $item->user->can('show', $item->device))) {
                    $this->idsToDelete[] = $item->id;
                    continue;
                }

                $this->setLanguage($item);

                Auth::loginUsingId($item->user_id);

                if (! empty($item->data['push'])) {
                    try {
                        sendNotification($item->user_id, $item);
                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                    }
                }

                try {
                    sendTemplateSMS($item->data['mobile_phone'], $this->smsTemplate, $item, $item->user_id);
                } catch (\Exception $e) {
                    Bugsnag::notifyException($e);
                }

                try {
                    sendTemplateEmail($item->data['email'], $this->emailTemplate, $item);
                } catch (\Exception $e) {
                    Bugsnag::notifyException($e);
                }


                if ( $webhookUrl = array_get($item->data, 'webhook')) {
                    try {
                        sendWebhook($webhookUrl, array_except($item->data, ['push', 'webhook']));
                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                    }
                }

                if ( ($command = array_get($item->data, 'command')) && $device = $item->device)
                {
                    try {
                        $protocolsManager = new Manager();
                        $protocol = $protocolsManager->protocol( $device->protocol );
                        $commandData = $protocol->buildCommand($device, $command);
                        send_command($commandData);
                    } catch (\Exception $e) {
                        Bugsnag::notifyException($e);
                    }
                }

                $item->delete();
            }

            if ( ! empty($this->idsToDelete)) {
                EventQueue::whereIn('id', $this->idsToDelete)->delete();

                $this->idsToDelete = [];
            }

            sleep(1);
        }

        return 'DONE';
    }

    private function setLanguage($item)
    {
        if ($item->user && $item->user->lang)
            return App::setLocale($item->user->lang);

        return App::setLocale(settings('main_settings.default_language'));
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