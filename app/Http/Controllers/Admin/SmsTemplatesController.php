<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Tobuli\Helpers\Templates\TemplateBuilderManager;
use Tobuli\Repositories\SmsTemplate\SmsTemplateRepositoryInterface as SmsTemplate;
use Tobuli\Validation\SmsTemplateFormValidator;

class SmsTemplatesController extends BaseController
{
    /**
     * @var SmsTemplate
     */
    private $smsTemplate;

    private $section = 'sms_templates';

    /**
     * @var SmsTemplateFormValidator
     */
    private $smsTemplateFormValidator;

    public function __construct(SmsTemplate $smsTemplate, SmsTemplateFormValidator $smsTemplateFormValidator)
    {
        parent::__construct();
        $this->smsTemplate = $smsTemplate;
        $this->smsTemplateFormValidator = $smsTemplateFormValidator;
    }

    public function index()
    {
        $input = Request::all();

        $items = $this->smsTemplate->searchAndPaginate($input, 'title');
        $section = $this->section;
        $page = $items->currentPage();
        $total_pages = $items->lastPage();
        $pagination = smartPaginate($items->currentPage(), $total_pages);
        $url_path = $items->resolveCurrentPath();

        return View::make('admin::'.ucfirst($this->section).'.'.(Request::ajax() ? 'table' : 'index'))->with(compact('items', 'input', 'section', 'pagination', 'page', 'total_pages', 'url_path'));
    }

    public function edit($id = null)
    {
        $item = $this->smsTemplate->find($id);
        if (empty($item)) {
            return modalError(dontExist('front.sms_template'));
        }

        $replacers = (new TemplateBuilderManager())->loadTemplateBuilder($item->name)->getReplacers();

        return View::make('admin::'.ucfirst($this->section).'.edit')->with(compact('item', 'replacers'));
    }

    public function update()
    {
        $input = Request::all();
        $id = $input['id'];

        try {
            $this->smsTemplateFormValidator->validate('update', $input, $id);

            $this->smsTemplate->update($id, $input);

            return Response::json(['status' => 1]);
        } catch (ValidationException $e) {
            return Response::json(['errors' => $e->getErrors()]);
        }
    }
}
