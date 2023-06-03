<?php

namespace ModalHelpers;

use App\Exceptions\Manager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

abstract class ModalHelper
{
    protected $user;

    protected $data;

    protected $api;

    protected $exceptionManager;

    public function __construct()
    {
        $this->api = boolval(Config::get('tobuli.api') == 1);
        $this->user = Auth::User();
        $this->data = request()->all();

        $this->exceptionManager = new Manager($this->user);
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function setApi($bool)
    {
        $this->api = boolval($bool);
    }

    public function checkException($repo, $action, $model = null)
    {
        $this->exceptionManager->check($repo, $action, $model);
    }
}
