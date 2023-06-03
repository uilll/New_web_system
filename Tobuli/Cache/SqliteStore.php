<?php

namespace Tobuli\Cache;

use Illuminate\Cache\DatabaseStore;
use Illuminate\Encryption\Encrypter;

class SqliteStore extends DatabaseStore
{
    public function __construct()
    {
        $config = config('cache.stores.sqlite');

        parent::__construct(
            $connection = app('db')->connection('sqlite'),
            $encrypter = new Encrypter(md5('encrypter-salt', true)),
            $config['table'],
            $config['prefix']
        );
    }
}
