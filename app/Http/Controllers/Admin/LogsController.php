<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class LogsController extends BaseController
{
    private $section = 'logs';

    private $logs_dir = '/opt/traccar/logs';

    public function __construct()
    {
        parent::__construct();

        $this->logs_dir = config('tobuli.logs_path');
    }

    public function index()
    {
        $items = File::files($this->logs_dir);

        $new_items = [];
        foreach ($items as $key => $item) {
            if (strpos($item, 'wrapper.log')) {
                unset($items[$key]);

                continue;
            }

            $name = last(explode('/', $item));
            $arr = explode('.', $name);
            $date = isset($arr['2']) ? substr($arr['2'], 0, 4).'-'.substr($arr['2'], 4, 2).'-'.substr($arr['2'], 6, 2) : date('Y-m-d');
            $new_items[$date] = [
                'name' => $name,
                'size' => File::size($item),
            ];
        }

        $items = $new_items;
        krsort($items);

        $section = $this->section;

        return view('admin::'.ucfirst($this->section).'.'.(Request::ajax() ? 'table' : 'index'))->with(compact('items', 'section'));
    }

    public function edit($id)
    {
        $id = str_replace('..', '', $id);

        return Response::download($this->logs_dir.'/'.$id);
    }

    public function destroy()
    {
        $ids = Input::get('id');

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        foreach ($ids as $id) {
            $id = str_replace('..', '', $id);
            if (! empty($id)) {
                @unlink($this->logs_dir.'/'.$id);
                $arr = explode('.', $id);
                @unlink($this->logs_dir.'/wrapper.log.'.(isset($arr['2']) ? $arr['2'] : date('Ymd')));
            }
        }

        return Response::json(['status' => 1]);
    }

    public function search($search_log = null)
    {
        dd('PESQUISA SUSPENSA TEMPORARIAMENTE');
        $status = false;

        if (! is_null($search_log)) {
            $search_log = strip_tags($search_log);
            /*$handle = @fopen("/opt/traccar/logs/tracker-server.log", "r");
                if ($handle)
                {
                    while (!feof($handle))
                    {
                        $buffer = fgets($handle);
                        if(strpos($buffer, $search_log) !== FALSE){
                            $matches[] = $buffer;
                            $status = true;
                        }
                    }
                    fclose($handle);
                }
            */
        }
        //dd('ok');

        //return view('admin::search_log.' . (Request::ajax() ? 'table' : 'index'))->with(compact('matches', 'status'));
        //return view('admin::.table');
    }
}
