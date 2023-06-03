<?php

namespace Tobuli\Protocols\Protocols;

use Tobuli\Protocols\Commands;
use Tobuli\Protocols\Protocol;

class Jt600Protocol extends BaseProtocol implements Protocol
{
    protected function commands()
    {
        return [
            $this->initCommand(Commands::TYPE_ENGINE_STOP),
            $this->initCommand(Commands::TYPE_ENGINE_RESUME),
            $this->initCommand(Commands::TYPE_SET_TIMEZONE),
            $this->initCommand(Commands::TYPE_REBOOT_DEVICE),
            $this->initCommand(Commands::TYPE_CUSTOM),
        ];
    }
}
