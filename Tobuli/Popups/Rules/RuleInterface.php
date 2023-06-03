<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.2.14
 * Time: 16.32
 */

namespace Tobuli\Popups\Rules;

interface RuleInterface
{
    public function getFields();

    public function doesApply();
}
