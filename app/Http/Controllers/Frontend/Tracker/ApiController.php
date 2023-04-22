<?php  namespace App\Http\Controllers\Frontend\Tracker;

use Illuminate\Routing\Controller;

use Tobuli\Entities\Device;
use Tobuli\Entities\Task;
use Tobuli\Entities\TaskStatus;
use Tobuli\Entities\User;
use Tobuli\Exceptions\ValidationException;

class ApiController extends Controller {

    protected $deviceInstance;

    public function __construct(Device $device)
    {
        $this->deviceInstance = $device;
    }

    public function login() {
        return response()->json(['success' => true, 'data' => ['device_id' => $this->deviceInstance->id]]);
    }
}