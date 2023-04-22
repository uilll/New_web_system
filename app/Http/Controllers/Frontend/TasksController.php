<?php

namespace App\Http\Controllers\Frontend;

use App\Events\NewTask;
use App\Http\Controllers\Controller;
use Facades\Repositories\TasksRepo;
use Facades\Repositories\UserRepo;
use Facades\Validators\TasksFormValidator;
use Tobuli\Entities\Task;
use Tobuli\Entities\TaskStatus;

use App\Exceptions\ResourseNotFoundException;
use App\Exceptions\PermissionException;
use Tobuli\Exceptions\ValidationException;

class TasksController extends Controller {

    public function index()
    {
        $this->checkException('tasks', 'view');

        $devices[0] = '-- '.trans('admin.select').' --';
        $devices += UserRepo::getDevices($this->user->id)->lists('name', 'id')->all();

        $statuses[0] = '-- '.trans('admin.select').' --';
        foreach (TaskStatus::$statuses as $key => $status) {
            $statuses[$key] = trans($status);
        }

        $priorities = [];
        foreach (Task::$priorities as $key => $priority) {
            $priorities[$key] = trans($priority);
        }

        $tasks = TasksRepo::searchAndPaginate(['filter' => ['user_id' => $this->user->id]], 'id', 'desc', 10);

        return view('front::Tasks.index')->with([
            'devices' => $devices,
            'priorities' => $priorities,
            'tasks' => $tasks,
            'statuses' => $statuses
        ]);
    }

    public function search()
    {
        $this->checkException('tasks', 'view');

        $filter =  ['user_id' => $this->user->id];
        if ($this->data['search_device_id'] != 0 )
            $filter['device_id'] = (int) $this->data['search_device_id'];

        if ($this->data['search_task_status'] != 0 )
            $filter['status'] = (int) $this->data['search_task_status'];

        if ($this->data['search_time_from'])
            $filter['delivery_time_from'] = (int) $this->data['search_time_from'];

        if ($this->data['search_time_to'])
            $filter['delivery_time_to'] = (int) $this->data['search_time_to'];
        $tasks = TasksRepo::searchAndPaginate(['filter' => $filter ], 'id', 'desc', 10);

        $devices[0] = '-- '.trans('admin.select').' --';
        $devices += UserRepo::getDevices($this->user->id)->lists('name', 'id')->all();

        $statuses[0] = '-- '.trans('admin.select').' --';
        foreach (TaskStatus::$statuses as $key => $status) {
            $statuses[$key] = trans($status);
        }

        return view('front::Tasks.list')->with(['tasks' => $tasks, 'devices' => $devices, 'statuses' => $statuses]);
    }

    public function store()
    {
        $this->checkException('tasks', 'store');

        TasksFormValidator::validate('create', $this->data);

        $task = new Task(request()->except('id'));
        $task->user_id = $this->user->id;
        $task->save();

        event(new NewTask($task));

        return ['status' => 1];
    }

    public function doDestroy($id) {
        $item = TasksRepo::find($id);

        $this->checkException('tasks', 'remove', $item);

        return view('front::Tasks.destroy')->with(['item' => $item]);
    }

    public function destroy() {
        $id = array_key_exists('task_id', $this->data) ? $this->data['task_id'] : $this->data['id'];

        $item = TasksRepo::findWithAttributes($id);

        $this->checkException('tasks', 'remove', $item);

        TasksRepo::delete($id);

        return ['status' => 1];
    }

    public function edit($id)
    {
        $item = TasksRepo::find($id);

        $this->checkException('tasks', 'edit', $item);

        $devices = UserRepo::getDevices($this->user->id)->lists('name', 'id')->all();

        $priorities = [];
        foreach (Task::$priorities as $key => $priority) {
            $priorities[$key] = trans($priority);
        }

        $statuses = [];
        foreach (TaskStatus::$statuses as $key => $status) {
            $statuses[$key] = trans($status);
        }

        return view('front::Tasks.edit')->with([
            'item' => $item,
            'devices' => $devices,
            'priorities' => $priorities,
            'statuses' => $statuses
        ]);
    }

    public function update() {
        $task = TasksRepo::findWithAttributes($this->data['id']);

        $this->checkException('tasks', 'update', $task);

        TasksFormValidator::validate('update', $this->data, $this->data['id']);

        $task->fill($this->data);
        $task->save();

        return ['status' => 1];
    }

    public function getSignature($taskStatusId) {
        $taskStatus = TaskStatus::find($taskStatusId);

        if ( ! $taskStatus)
            throw new ResourseNotFoundException('global.task_status');

        if ( ! $taskStatus->signature)
            throw new ResourseNotFoundException('global.task_status_1456');

        return response($taskStatus->signature)
            ->header('Content-Type', 'image/jpeg')
            ->header('Pragma', 'public')
            ->header('Content-Disposition', 'inline; filename="photo.jpeg"')
            ->header('Cache-Control', 'max-age=60, must-revalidate');
    }
}