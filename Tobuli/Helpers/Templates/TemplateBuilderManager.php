<?php

namespace Tobuli\Helpers\Templates;

use Tobuli\Helpers\Templates\Builders as Builders;

class TemplateBuilderManager
{
    public function loadTemplateBuilder($template)
    {
        switch ($template) {
            case 'event':
                $template_builder = Builders\EventTemplate::class;
                break;
            case 'report':
                $template_builder = Builders\ReportTemplate::class;
                break;
            case 'service_expiration':
                $template_builder = Builders\ServiceExpirationTemplate::class;
                break;
            case 'service_expired':
                $template_builder = Builders\ServiceExpiredTemplate::class;
                break;
            case 'registration':
                $template_builder = Builders\RegistrationTemplate::class;
                break;
            case 'account_created':
                $template_builder = Builders\AccountCreatedTemplate::class;
                break;
            default:
                throw new \Exception('Not found template builder for template');
        }

        return new $template_builder();
    }
}
