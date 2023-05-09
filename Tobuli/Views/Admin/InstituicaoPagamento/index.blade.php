@extends('Admin.Layouts.default')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4>Gerenciar Instituições de Pagamento</h4>
                </div>
                <div class="panel-body">

                    <a href="javascript:" type="button" class="btn btn-success btn-sm pull-right" data-modal="instituicao_pagamento_create" data-url="{{ route('instituicao_pagamento.create') }}">
                        <i class="glyphicon glyphicon-plus"></i> Nova Instituição de Pagamento
                    </a>
                    
                    <br><br>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nome da Conta</th>
                                <th>Nome da Instituição</th>
                                <th>Usuários Permitidos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($instituicoes as $instituicao)
                                <tr>
                                    <td>{{ $instituicao->id }}</td>
                                    <td>{{ $instituicao->nome_conta }}</td>
                                    <td>{{ $instituicao->nome_instituicao }}</td>
                                    <td>{{ $instituicao->usuarios_permitidos }}</td>
                                    <td>
                                        <a href="javascript:" type="button" class="btn btn-primary btn-sm" data-modal="instituicao_pagamento_create" data-url="{{ route('instituicao_pagamento.edit', $instituicao->id) }}">
                                            <i class="glyphicon glyphicon-edit"></i> Editar
                                        </a>
                                        <form action="{{ route('instituicao_pagamento.destroy', $instituicao->id) }}" method="POST" style="display: inline-block;">
                                            {{ csrf_field() }}
                                            {{ method_field('DELETE') }}
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="glyphicon glyphicon-trash"></i> Excluir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
