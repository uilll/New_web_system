<div class="table-responsive">
    <table class="table table-list">
        <thead>
        <tr>
            {!! tableHeader('validation.attributes.title') !!}
            {!! tableHeader('validation.attributes.device_id') !!}
            {!! tableHeader('validation.attributes.status') !!}
            {!! tableHeader('validation.attributes.priority') !!}
            <th></th>
        </tr>
        </thead>
        <tbody>
        @if (count($tasks))
            @foreach ($tasks as $task)
                <tr>
                    <td>
                        {{$task->title}}
                    </td>
                    <td>
                        {{$task->deviceName}}
                    </td>
                    <td>
                        {{$task->statusName}}
                    </td>
                    <td>
                        {{$task->priorityName}}
                    </td>
                    <td class="actions">
                        @if ($task->lastStatus && $task->lastStatus->signature)
                        <a href="{!!route('tasks.signature', $task->lastStatus->id)!!}" class="btn icon download" download></a>
                        @endif
                        <a href="javascript:" class="btn icon edit" data-url="{!!route('tasks.edit', $task->id)!!}" data-modal="tasks_edit"></a>
                        <a href="javascript:" class="btn icon delete" data-url="{!!route('tasks.do_destroy', $task->id)!!}" data-modal="tasks_destroy"></a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="5">{!!trans('front.no_tasks')!!}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>
<div class="nav-pagination">
    @if (count($tasks))
        {!! $tasks->setPath(route('tasks.list'))->render() !!}
    @endif
</div>