<?php

use Illuminate\Database\Seeder;
use Tobuli\Repositories\EmailTemplate\EmailTemplateRepositoryInterface as EmailTemplate;

class EmailTemplatesTableSeeder extends Seeder
{
    /**
     * @var EmailTemplate
     */
    private $emailTemplate;

    public function __construct(EmailTemplate $emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;
    }

    public function run()
    {
        $this->emailTemplate->create([
            'name' => 'event',
            'title' => 'New event',
            'note' => 'Hello,<br><br>Event: [event]<br>Geofence: [geofence]<br>Device: [device]<br>Address: [address]<br>Position: [position]<br>Altitude: [altitude]<br>Speed: [speed]<br>Time: [time]',
        ]);

        $this->emailTemplate->create([
            'name' => 'service_expiration',
            'title' => 'Manutenção agendada',
            'note' => 'Olá, expira hoje<br><br> [expiration_date]<br><br> o dia da manutenção<br><br> [service]<br><br> do veículo<br><br> [device]. Por favor realize a manutenção neste veículo.',
        ]);

        $this->emailTemplate->create([
            'name' => 'report',
            'title' => 'Relatório "[name]"',
            'note' => 'Olá,<br><br>Nome: [name]<br>Periodo: [period]',
        ]);

        $this->emailTemplate->create([
            'name' => 'service_expired',
            'title' => 'Manutenção agendada',
            'note' => 'Olá, expira hoje<br><br> [expiration_date]<br><br> o dia da manutenção<br><br> [service]<br><br> do veículo<br><br> [device]. Por favor realize a manutenção neste veículo.',
        ]);

        $this->emailTemplate->create([
            'name' => 'registration',
            'title' => 'Registration confirmation',
            'note' => 'Hello,<br><br>Thank you for registering, here\'s your login information:<br>Email: [email]<br>Password: [password]',
        ]);
    }
}
