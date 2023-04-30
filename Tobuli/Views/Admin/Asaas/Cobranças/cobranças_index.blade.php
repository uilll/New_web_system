@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" id="table_cobranças_asaas">

    <div class="panel-heading">
        <ul class="nav nav-tabs nav-icons pull-right">
            @if( Auth::User()->perm('finances', 'edit'))
            <li role="presentation" class="">
                <a href="javascript:" type="button" class="" data-modal="cobranças_asaas_cobrar" data-url="{{ route("asaas.cobranças.cobrar") }}">
                    <i class="icon plus" title="Nova cobrança Asaas"></i>
                </a>
            </li>
            <li role="presentation" class="">
                
            </li>
            @endif
        </ul>

        <div class="panel-title"><i class="icon check"></i> Cobranças - Asaas</div>

        <div class="panel-form">
            <div class="form-group search">
                {!! Form::open(['route' => 'asaas.cobranças.listarCobranças', 'method' => 'get']) !!}
                {!! Form::text('search_phrase', null, ['id' => 'search_asaas', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">Cobranças Asaas</span>
            </div>
        </div>
    </div>
        
    <div class="table_error"></div>
    <div class="table-responsive">
        <table class="table table-list" data-toggle="multiCheckbox">
            <thead>
            <tr>
                {!! tableHeader('admin.actions', 'style="text-align: right;"') !!}
                {!! tableHeader('status') !!}
                {!! tableHeader('Nome') !!}
                {!! tableHeader('ID') !!}
                {!! tableHeader('Forma de Pagamento') !!}
                {!! tableHeader('Valor') !!}
                {!! tableHeader('Data de Criação') !!}                
                {!! tableHeader('Data de Vencimento') !!}
                {!! tableHeader('Descrição') !!}
            </tr>
            </thead>
            
            <tbody>
                <?php
                  $collor = true;  
                  //$cobranças_ = $cobranças->items();
                  //dd($cobranças->items());
                ?>
                @foreach ($cobranças->items() as $cobrança)
                
                <?php
                    $collor = !$collor;  
                    //dd($cobrança);
                ?>
                <tr style="color: {{ 'true' ? 'black' : ($collor ? 'white' : 'red') }}; background-color: {{$collor ? 'gray' : '#fffff'}}">
                    @if (true)
                        <td class="actions">
                            @if (Auth::User()->perm('finances', 'edit') || Auth::User()->perm('finances', 'remove'))
                                <div class="btn-group dropdown droparrow" data-position="fixed">
                                    <i class="btn icon edit" data-toggle="dropdown" aria-haspopup="true"
                                    aria-expanded="true"></i>
                                    <ul class="dropdown-menu">
                                        @if( Auth::User()->perm('finances', 'edit') )
                                            <li>
                                                <a href="javascript:" data-modal="finances_edit" data-url="{{ route("asaas.cobranças.editCo", ['id' => $cobrança['id']]) }}">
                                                    {{ trans('global.edit') }}
                                                </a>
                                            </li>
                                        @endif
                                        @if( Auth::User()->perm('finances', 'edit') )
                                            <li>
                                                <a href="javascript:" data-modal="finances_pagar" data-url="{{ route("asaas.cobranças.pagar", ['id' => $cobrança['id']]) }}">
                                                    {{ 'Pagar' }}
                                                </a>
                                            </li>
                                        @endif
                                        @if( Auth::User()->perm('finances', 'remove') )
                                            <li>
                                                <a href="javascript:" data-modal="finances_remove" data-url="{{ route("asaas.cobranças.deleteCo", ['id' => $cobrança['id']]) }}">
                                                    {{ 'Excluir' }}
                                                </a>
                                            </li>
                                        @endif    
                                    </ul>
                                </div>
                            @endif
                        </td>
                    @endif
                    <td>
                        {{ $cobrança['status'] }}
                    </td>
                    <td>
                        {{ $cobrança['name'] }}
                    </td>
                    <td>
                        {{ $cobrança['customer'] }}
                    </td>
                    <td>
                        {{ $cobrança['billingType'] }} 
                    </td>
                    <td>
                        {{ $cobrança['value'] }}
                    </td>
                    <td>
                        {{ $cobrança['dateCreated']  }}
                    </td>
                    <td>
                        {{ $cobrança['dueDate']  }}
                    </td>
                    <td>
                        {{ $cobrança['description'] }}
                    </td>
                </tr>
                <tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="nav-pagination">
        {!! $cobranças->setPath(url().'/admin/asaas/cobranças/')->render() !!}
    </div>
</div>

@stop