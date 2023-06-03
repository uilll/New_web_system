<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesResources;
use App\Exceptions\Manager;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class Controller extends BaseController
{
    use AuthorizesRequests, AuthorizesResources, DispatchesJobs, ValidatesRequests;

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
