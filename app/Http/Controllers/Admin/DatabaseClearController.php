<?php namespace App\Http\Controllers\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Tobuli\Repositories\Config\ConfigRepositoryInterface as Config;
use Tobuli\Validation\AdminDatabaseClearFormValidator;
use Tobuli\Exceptions\ValidationException;

class DatabaseClearController extends BaseController {
    /**
     * @var Config
     */
    private $config;
    /**
     * @var AdminDatabaseClearFormValidator
     */
    private $adminBackupsFormValidator;

    function __construct(Config $config, AdminDatabaseClearFormValidator $adminDatabaseClearFormValidator) {
        parent::__construct();
        $this->config = $config;
        $this->adminDatabaseClearFormValidator = $adminDatabaseClearFormValidator;
    }

    public function panel() {
        $settings = settings('db_clear');

        $size = getDatabaseSize(['gpswox_traccar','gpswox_web']);
        $size = formatBytes( $size );

        return View::make('admin::DatabaseClear.panel')->with(compact('settings', 'size'));
    }

    public function save() {
        $input = Input::all();

        try
        {
            $this->adminDatabaseClearFormValidator->validate('update', $input);

            $data = [
                'status' => ! empty($input['status']),
                'days' => $input['days']
            ];

            settings('db_clear', $data);

            return Redirect::route('admin.tools.index')->withSuccess(trans('front.successfully_saved'));
        }
        catch (ValidationException $e)
        {
            return Redirect::route('admin.tools.index')->withInput()->withErrors($e->getErrors());
        }
    }
}
