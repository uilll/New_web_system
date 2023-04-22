<?php

namespace Tobuli\Entities\File;


class DeviceMedia extends FileEntity
{
    protected function getDirectory($device)
    {
        return str_finish(config('tobuli.media_path'), '/') . $device->imei;
    }
}