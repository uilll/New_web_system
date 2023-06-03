<?php

namespace Tobuli\Helpers\Templates\Builders;

use Tobuli\Helpers\Templates\TemplateBuilder;

class RegistrationTemplate extends TemplateBuilder
{
    /**
     * @return array
     */
    protected function getReplaceVariables($item)
    {
        return [
            '[email]' => $item->email,
            '[password]' => $item->password_to_email,
        ];
    }

    /**
     * @return array
     */
    public function getReplacers()
    {
        return [
            '[email]' => 'User email',
            '[password]' => 'User password',
        ];
    }
}
