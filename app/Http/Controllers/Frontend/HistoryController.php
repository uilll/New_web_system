<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\HistoryModalHelper;

class HistoryController extends Controller
{
    public function index()
    {
        $data = HistoryModalHelper::get();
        //dd($data);

        return is_array($data) && ! $this->api ? view('front::History.index')->with($data) : $data;
    }

    public function positionsPaginated()
    {
        $data = HistoryModalHelper::getMessages();

        return ! $this->api ? view('front::History.partials.bottom_messages')->with($data) : $data;
    }

    public function doDeletePositions()
    {
        return view('front::History.do_delete');
    }

    public function deletePositions()
    {
        HistoryModalHelper::deletePositions();

        return HistoryModalHelper::deletePositions();
    }

    public function getPosition()
    {
        return HistoryModalHelper::getPosition();
    }
}
