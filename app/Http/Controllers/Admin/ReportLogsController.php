<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\ReportLogModalHelper;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;

class ReportLogsController extends Controller
{
    private $section = 'report_logs';

    public function index()
    {
        $data = [];
        $data['logs'] = ReportLogModalHelper::get();
        $data['section'] = $this->section;
        $data['page'] = $data['logs']->currentPage();
        $data['total_pages'] = $data['logs']->lastPage();
        $data['pagination'] = smartPaginate($data['logs']->currentPage(), $data['total_pages']);
        $data['url_path'] = $data['logs']->resolveCurrentPath();

        return view('admin::'.ucfirst($this->section).'.'.(Request::ajax() ? 'table' : 'index'))->with($data);
    }

    public function edit($id)
    {
        $data = ReportLogModalHelper::download($id);

        return Response::make($data['data'], 200, $data['headers']);
    }

    public function destroy()
    {
        $data = ReportLogModalHelper::destroy();

        return Request::ajax() ? Response::json($data) : null;
    }
}
