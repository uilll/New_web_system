<?php
use Tobuli\Repositories\SmsTemplate\SmsTemplateRepositoryInterface as SmsTemplate;
use Illuminate\Database\Seeder;

class SmsTemplatesTableSeeder extends Seeder {

    /**
     * @var SmsTemplate
     */
    private $smsTemplate;

    public function __construct(SmsTemplate $smsTemplate)
    {
        $this->smsTemplate = $smsTemplate;
    }

	public function run()
	{
        $this->smsTemplate->create([
            'name' => 'event',
            'title' => 'New event',
            'note' => 'Hello,\r\nEvent: [event]\r\nGeofence: [geofence]\r\nDevice: [device]\r\nTime: [time]'
        ]);
        $this->smsTemplate->create([
            'name' => 'report',
            'title' => 'Relatório "[name]"',
            'note' => 'Olá,\r\nNome: [name]\r\nPeríodo: [period]'
        ]);
        $this->smsTemplate->create([
            'name' => 'service_expiration',
            'title' => 'Service expiration',
            'note' => 'Olá, em breve irá expirar hoje<br><br> [expiration_date]<br><br> o dia da manutenção<br><br> [service]<br><br> do veículo<br><br> [device]. Por favor realize a manutenção neste veículo.'
        ]);
        $this->smsTemplate->create([
            'name' => 'service_expired',
            'title' => 'Service expired',
            'note' => 'Olá, expira hoje<br><br> [expiration_date]<br><br> o dia da manutenção<br><br> [service]<br><br> do veículo<br><br> [device]. Por favor realize a manutenção neste veículo.'
        ]);

	}

}