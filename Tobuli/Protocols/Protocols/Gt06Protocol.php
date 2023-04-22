<?php

namespace Tobuli\Protocols\Protocols;

use Tobuli\Protocols\Protocol;
use Tobuli\Protocols\Commands;

class Gt06Protocol extends BaseProtocol implements Protocol
{
    protected function commands()
    {
        return [
            $this->initCommand(Commands::TYPE_ENGINE_STOP),
            $this->initCommand(Commands::TYPE_ENGINE_RESUME),
            $this->initCommand(Commands::TYPE_CUSTOM),
        ];
    }
}