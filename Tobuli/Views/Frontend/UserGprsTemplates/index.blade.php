<div class="table-responsive">
    <table class="table table-list">
        <thead>
            <tr>
                {!! tableHeader('validation.attributes.title') !!}
                {!! tableHeader('validation.attributes.protocol') !!}
                <th></th>
            </tr>
        </thead>
        <tbody>
        @if (count($user_gprs_templates))
            @foreach ($user_gprs_templates as $user_gprs_template)
                <tr>
                    <td>
                        {{$user_gprs_template->title}}
                    </td>
                    <td>
                        {{$user_gprs_template->protocol}}
                    </td>
                    <td class="actions">
                        <a href="javascript:" class="btn icon edit" data-url="{!!route('user_gprs_templates.edit', $user_gprs_template->id)!!}" data-modal="user_gprs_templates_edit"></a>
                        <a href="javascript:" class="btn icon delete" data-url="{!!route('user_gprs_templates.do_destroy', $user_gprs_template->id)!!}" data-modal="user_gprs_templates_destroy"></a>
                    </td>
                </tr>
            @endforeach
        @else
            <tr>
                <td class="no-data" colspan="2">{!!trans('front.no_templates')!!}</td>
            </tr>
        @endif
        </tbody>
    </table>
</div>

<div class="nav-pagination">
    {!! $user_gprs_templates->setPath(route('user_gprs_templates.index'))->render() !!}
</div>