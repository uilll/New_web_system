<?php

namespace Tobuli\Helpers;

class Backup
{
    protected $settings;

    protected $ftp;

    protected $hive;

    public function __construct(array $settings = [])
    {
        $this->hive = new Hive();

        if (empty($settings)) {
            $settings = settings('backups');
        }

        $this->settings = $settings;
    }

    public function setupFTP()
    {
        $settings = $this->settings;

        if (! empty($settings['type']) && $settings['type'] == 'auto') {
            $hiveSettings = $this->hive->getBackupServer();

            if ($hiveSettings) {
                $settings = array_merge($this->settings, $hiveSettings);
            }
        }

        $this->ftp = new BackupFTP(
            $settings['ftp_server'],
            $settings['ftp_username'],
            $settings['ftp_password'],
            $settings['ftp_port'],
            $settings['ftp_path']
        );
    }

    public function auto()
    {
        if (isset($this->settings['next_backup']) && time() < $this->settings['next_backup']) {
            return false;
        }

        $this->setupFTP();

        $this->setNextBackup();

        if (empty($this->settings['ftp_server'])) {
            return false;
        }

        try {
            $this->db();
            $this->images();

            $this->setMessage(trans('front.successfully_uploaded'), 1);
        } catch(\Exception $e) {
            $this->setMessage(trans('front.unexpected_error'), 0);
        }
    }

    public function images()
    {
        $this->filesystem(images_path());
    }

    public function filesystem($path)
    {
        $command = 'tar -cv '.$path;
        $filename = basename($path).'.tar';

        $this->ftp()->process($command, $filename);
    }

    public function db()
    {
        $command = 'mysqldump --single-transaction=TRUE --lock-tables=false -h '.config('database.connections.mysql.host').' -u '.config('database.connections.mysql.username').' --password='.config('database.connections.mysql.password').' --databases '.config('database.connections.mysql.database').' '.config('database.connections.traccar_mysql.database');
        $filename = 'db.sql';

        $this->ftp()->process($command, $filename);
    }

    public function check()
    {
        if (! $this->ftp()->check()) {
            throw new \Exception(trans('front.login_failed'));
        }

        try {
            $this->ftp()->process('echo "test"', 'test.txt', false);
        } catch (\Exception $e) {
            throw new \Exception(trans('front.unexpected_error'));
        }
    }

    protected function setMessage($message, $status)
    {
        if (! isset($this->settings['messages'])) {
            $this->settings['messages'] = [];
        }

        array_unshift($this->settings['messages'], [
            'status' => $status,
            'date' => date('Y-m-d H:i'),
            'path' => $this->settings['ftp_path'],
            'message' => $message,
        ]);

        $this->settings['messages'] = array_slice($this->settings['messages'], 0, 5);

        settings('backups', $this->settings);
    }

    protected function setNextBackup()
    {
        $this->settings['next_backup'] = strtotime(date('Y-m-d', strtotime('+'.$this->settings['period'].' days')).' '.$this->settings['hour']);

        settings('backups.next_backup', $this->settings['next_backup']);
    }

    protected function ftp()
    {
        if (is_null($this->ftp)) {
            $this->setupFTP();
        }

        return $this->ftp;
    }
}
