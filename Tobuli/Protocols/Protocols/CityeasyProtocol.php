<?php

namespace Tobuli\Protocols\Protocols;

use Tobuli\Protocols\Commands;
use Tobuli\Protocols\Protocol;

class CityeasyProtocol extends BaseProtocol implements Protocol
{
    protected function commands()
    {
        return [
            $this->initCommand(Commands::TYPE_POSITION_SINGLE),
            $this->initCommand(Commands::TYPE_POSITION_PERIODIC),
            $this->initCommand(Commands::TYPE_POSITION_STOP),
            $this->initCommand(Commands::TYPE_SET_TIMEZONE),
            $this->initCommand(Commands::TYPE_CUSTOM),
        ];
    }
}
