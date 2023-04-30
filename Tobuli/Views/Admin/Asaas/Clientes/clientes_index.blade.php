@extends('Admin.Layouts.default')

@section('content')
<div class="panel panel-default" id="table_clientes_asaas">

    <div class="panel-heading">
        <ul class="nav nav-tabs nav-icons pull-right">
            @if( Auth::User()->perm('finances', 'edit') )
            <li role="presentation" class="">
                <a href="javascript:" type="button" class="" data-modal="clientes_asaas_create" data-url="{{ route("asaas.clientes.create") }}">
                    <i class="icon plus" title="Novo cliente Asaas"></i>
                </a>
            </li>
            <li role="presentation" class="">
                
            </li>
            @endif
        </ul>

        <div class="panel-title"><i class="icon check"></i> Clientes - Asaas</div>

        <div class="panel-form">
            <div class="form-group search">
                {!! Form::open(['route' => 'asaas.clientes.listarClientes', 'method' => 'get']) !!}
                {!! Form::text('search_phrase', null, ['id' => 'search_asaas', 'class' => 'form-control', 'placeholder' => trans('admin.search_it'), 'data-filter' => true]) !!}
                <span id='search_menu' style="display:none">Clientes Asaas</span>
            </div>
        </div>
    </div>
        
    <div class="table_error"></div>
    <div class="table-responsive">
        <table class="table table-list" data-toggle="multiCheckbox">
            <thead>
            <tr>
                {!! tableHeader('admin.actions', 'style="text-align: right;"') !!}
                {!! tableHeader('ID') !!}
                {!! tableHeader('Nome') !!}
                {!! tableHeader('E-mail') !!}
                {!! tableHeader('CPF/CNPJ') !!}
                {!! tableHeader('Telefone') !!}
                {!! tableHeader('Celular', 'style="text-align:center;"') !!}
                {!! tableHeader('Endereço') !!}
                {!! tableHeader('Número') !!}
                {!! tableHeader('Complemento') !!}
                {!! tableHeader('Bairro') !!}
                {!! tableHeader('Cidade') !!}
                {!! tableHeader('Estado') !!}
                {!! tableHeader('CEP') !!}
            </tr>
            </thead>
            
            <tbody>
                <?php
                  $collor = true;  
                ?>
                @foreach ($clientes as $cliente)
                
                    <?php
                        $collor = !$collor;  
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
                                                    <a href="javascript:" data-modal="finances_edit"
                                                    data-url="{{ route("asaas.clientes.edit", ['id' => $cliente['id']]) }}">
                                                        {{ trans('global.edit') }}
                                                    </a>
                                                </li>
                                            @endif
                                            @if( Auth::User()->perm('finances', 'remove') )
                                                <li>
                                                    <a href="javascript:" data-modal="finances_remove"
                                                    data-url="{{ route("asaas.clientes.delete", ['id' => $cliente['id']]) }}">
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
                            {{$cliente['id']}} 
                        </td>
                        
                        <td>
                            {{ $cliente['name'] }} 
                        </td>
                        <td>
                            {{ $cliente['email'] }}
                        </td>
                        <td>
                            {{ $cliente['cpfCnpj']  }}
                        </td>
                        <td>
                            {{ $cliente['phone'] }}
                        </td>
                        <td>
                            {{ $cliente['mobilePhone']}}
                        </td>
                        <td>
                            {{ $cliente['address'] }}
                        </td>
                        <td>
                            {{ $cliente['addressNumber'] }}
                        </td>
                        <td>
                            {{ $cliente['complement'] }}
                        </td>
                        
                        <td>
                            {{ $cliente['province'] }}
                        </td>
                        <td>
                            {{ $cliente['city'] }}
                        </td>
                        <td>
                            {{ $cliente['state'] }}
                        </td>
                        <td>
                            {{ $cliente['postalCode'] }}
                        </td>
                    </tr>
                    <tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="nav-pagination">
        {!! $clientes->setPath(url().'/admin/asaas/clientes/')->render() !!}
    </div>
    

</div>

@stop