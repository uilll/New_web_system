<?php

namespace Tobuli\Helpers\Templates\Builders;

use Tobuli\Helpers\Templates\TemplateBuilder;

class AccountCreatedTemplate extends TemplateBuilder
{
    /**
     * @return array
     */
    protected function getReplaceVariables($item)
    {
        return [
            '[email]' => $item['email'],
            '[password]' => $item['password'],
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
