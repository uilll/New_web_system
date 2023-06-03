<?php

namespace App\Http\Controllers;

use App\Exceptions\Manager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected $data;

    protected $api;

    protected $user;

    protected $exceptionManager;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->user = Auth::User();
        $this->api = boolval(Config::get('tobuli.api') == 1);
        $this->data = request()->all();

        $this->exceptionManager = new Manager($this->user);
    }

    public function checkException($repo, $action, $model = null)
    {
        $this->exceptionManager->check($repo, $action, $model);
    }
}
