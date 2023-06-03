<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Tobuli\Exceptions\ValidationException;
use Tobuli\Repositories\EventCustom\EventCustomRepositoryInterface as EventCustom;
use Tobuli\Repositories\TrackerPort\TrackerPortRepositoryInterface as TrackerPort;
use Tobuli\Validation\EventCustomFormValidator;

class EventsController extends BaseController
{
    private $section = 'events';

    /**
     * @var EventCustom
     */
    private $eventCustom;

    /**
     * @var EventCustomFormValidator
     */
    private $eventCustomFormValidator;

    public function __construct(EventCustom $eventCustom, EventCustomFormValidator $eventCustomFormValidator)
    {
        parent::__construct();
        $this->eventCustom = $eventCustom;
        $this->eventCustomFormValidator = $eventCustomFormValidator;
    }

    public function index()
    {
        $input = Request::all();
        $input['filter']['user_id'] = null;

        $items = $this->eventCustom->searchAndPaginate($input, 'message');
        $section = $this->section;
        $page = $items->currentPage();
        $total_pages = $items->lastPage();
        $pagination = smartPaginate($items->currentPage(), $total_pages);
        $url_path = $items->resolveCurrentPath();

        return View::make('admin::'.ucfirst($this->section).'.'.(Request::ajax() ? 'table' : 'index'))->with(compact('items', 'input', 'section', 'pagination', 'page', 'total_pages', 'url_path'));
    }

    public function create(TrackerPort $trackerPortRepo)
    {
        $protocols = $trackerPortRepo->getProtocolList();
        $types = ['1' => trans('front.event_type_1'), '2' => trans('front.event_type_2'), '3' => trans('front.event_type_3')];
        if ($this->api) {
            $protocols_arr = [];
            foreach ($protocols as $key => $value) {
                array_push($protocols_arr, ['id' => $key, 'value' => $value]);
            }
            $protocols = $protocols_arr;

            $types = [['id' => '1', 'value' => trans('front.event_type_1')], ['id' => '2', 'value' => trans('front.event_type_2')], ['id' => '3', 'value' => trans('front.event_type_3')]];
        }

        return View::make('admin::'.ucfirst($this->section).'.create')->with(compact('protocols', 'types'));
    }

    public function store()
    {
        $input = Request::all();

        try {
            $this->eventCustomFormValidator->validate('create', $input);

            $insert = false;
            foreach ($input['tag'] as $key => $tag) {
                $tag = strtolower($tag);
                $type = $input['type'][$key];
                $tag_value = $input['tag_value'][$key];
                if ($tag == '' && $tag_value == '') {
                    continue;
                }

                if ($tag == '' || $tag_value == '') {
                    throw new ValidationException(['conditions' => trans('front.fill_all_fields')]);
                }

                $insert = true;
                $input['conditions'][] = [
                    'tag' => $tag,
                    'type' => $type,
                    'tag_value' => $tag_value,
                ];
            }

            if (! $insert) {
                throw new ValidationException(['conditions' => trans('front.fill_all_fields')]);
            }

            $item = $this->eventCustom->create($input + ['always' => isset($input['alawys'])]);

            $tags_arr = [];
            foreach ($input['conditions'] as $condition) {
                $tags_arr[$condition['tag']] = [
                    'event_custom_id' => $item->id,
                    'tag' => $condition['tag'],
                ];
            }
            DB::table('event_custom_tags')->insert($tags_arr);

            return Response::json(['status' => 1]);
        } catch (ValidationException $e) {
            return Response::json(['errors' => $e->getErrors()]);
        }
    }

    public function edit($id, TrackerPort $trackerPortRepo)
    {
        $item = $this->eventCustom->find($id);
        if (empty($item)) {
            return modalError(dontExist('global.event'));
        }

        $protocols = $trackerPortRepo->getProtocolList();

        $types = [
            '1' => trans('front.event_type_1'),
            '2' => trans('front.event_type_2'),
            '3' => trans('front.event_type_3'),
        ];

        if ($this->api) {
            $protocols_arr = [];
            foreach ($protocols as $key => $value) {
                array_push($protocols_arr, ['id' => $key, 'value' => $value]);
            }
            $protocols = $protocols_arr;

            $types = [['id' => '1', 'value' => trans('front.event_type_1')], ['id' => '2', 'value' => trans('front.event_type_2')], ['id' => '3', 'value' => trans('front.event_type_3')]];
        }

        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('item', 'protocols', 'types'));
    }

    public function update()
    {
        $input = Request::all();
        $id = $input['id'];
        $item = $this->eventCustom->find($input['id']);

        try {
            $this->eventCustomFormValidator->validate('update', $input, $id);

            $insert = false;
            $tags_arr = [];
            foreach ($input['tag'] as $key => $tag) {
                $tag = strtolower($tag);
                $type = $input['type'][$key];
                $tag_value = $input['tag_value'][$key];
                if ($tag == '' && $tag_value == '') {
                    continue;
                }

                if ($tag == '' || $tag_value == '') {
                    throw new ValidationException(['conditions' => trans('front.fill_all_fields')]);
                }

                $insert = true;

                $tags_arr[$tag] = [
                    'event_custom_id' => $item->id,
                    'tag' => $tag,
                ];
                $input['conditions'][] = [
                    'tag' => $tag,
                    'type' => $type,
                    'tag_value' => $tag_value,
                ];
            }

            if (! $insert) {
                throw new ValidationException(['conditions' => trans('front.fill_all_fields')]);
            }

            $this->eventCustom->update($item->id, $input + ['always' => isset($input['alawys'])]);
            $item->tags()->delete();
            DB::table('event_custom_tags')->insert($tags_arr);

            return Response::json(['status' => 1]);
        } catch (ValidationException $e) {
            return Response::json(['errors' => $e->getErrors()]);
        }
    }

    public function destroy()
    {
        $ids = Request::get('id');
        if (is_array($ids) && $nr = count($ids)) {
            foreach ($ids as $id) {
                $this->eventCustom->delete($id);
            }
        }

        return Response::json(['status' => 1]);
    }
}
