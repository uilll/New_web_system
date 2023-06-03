<?php

namespace Tobuli\Helpers\Templates;

use Tobuli\Exceptions\ValidationException;

abstract class TemplateBuilder
{
    abstract protected function getReplaceVariables($item);

    abstract public function getReplacers();

    /**
     * @param $item
     * @param $for
     * @return array
     *
     * @throws ValidationException
     */
    public function buildTemplate($template, $data = null)
    {
        return [
            'subject' => $this->replaceVariables($template->title, $data),
            'body' => $this->replaceVariables($template->note, $data),
        ];
    }

    /**
     * @return string
     */
    protected function replaceVariables($replace_target, $item)
    {
        $variables = $this->getReplaceVariables($item);

        return strtr($replace_target, $variables);
    }
}
