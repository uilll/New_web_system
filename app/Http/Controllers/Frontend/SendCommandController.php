<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\SendCommandModalHelper;

class SendCommandController extends Controller
{
    public function create()
    {
        $data = SendCommandModalHelper::createData();

        //debugar(true, $data);

        return ! $this->api ? view('front::SendCommand.create')->with($data) : $data;
    }

    public function store()
    {
        return SendCommandModalHelper::create();
    }

    public function gprsStore()
    {
        return SendCommandModalHelper::gprsCreate();
    }

    public function getDeviceSimNumber()
    {
        return SendCommandModalHelper::getDeviceSimNumber();
    }

    public function getCommands()
    {
        return SendCommandModalHelper::getDeviceCommands();
    }
}
