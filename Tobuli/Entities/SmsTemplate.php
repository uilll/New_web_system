<?php namespace Tobuli\Entities;

use Eloquent;
use Tobuli\Helpers\Templates\TemplateBuilderManager;

class SmsTemplate extends Eloquent {
	protected $table = 'sms_templates';

    protected $fillable = array('title', 'note');

    public $timestamps = false;

    public function buildTemplate($data)
    {
        $template_builder = (new TemplateBuilderManager())->loadTemplateBuilder($this->name);

        return $template_builder->buildTemplate($this, $data);
    }
}
