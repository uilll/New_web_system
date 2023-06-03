<?php

namespace Tobuli\Protocols\Protocols;

use Tobuli\Protocols\Commands;
use Tobuli\Protocols\Protocol;

class TotemProtocol extends BaseProtocol implements Protocol
{
    protected $passwordRequired = true;

    protected function commands()
    {
        return [
            $this->initCommand(Commands::TYPE_ENGINE_STOP),
            $this->initCommand(Commands::TYPE_ENGINE_RESUME),
            $this->initCommand(Commands::TYPE_CUSTOM),
        ];
    }
}
