<?php

namespace App\Http\Controllers\Admin;

use App\instituicao_pagamento;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class InstituicaoPagamentoController extends BaseController
{
    public function index()
    {
        $instituicoes = instituicao_pagamento::all();
        foreach ($instituicoes as $instituicao) {
            // Decodifica a lista de usuários permitidos em uma matriz
            $usuariosPermitidos = json_decode($instituicao->usuarios_permitidos);

            // Obtém os e-mails e ids dos usuários
            $usuarios = DB::table('users')
                            ->where('active', 1)
                            ->whereIn('id', $usuariosPermitidos)
                            ->pluck('email', 'id');
            //dd($usuarios);
            $instituicao->usuarios_permitidos = $usuarios;
            //dd( $instituicao);
        }

        return view('admin::InstituicaoPagamento.index', compact('instituicoes'));
    }

    public function create()
    {
        $lista = DB::table('users')->where('active', 1)->lists('email', 'id');

        return view('admin::InstituicaoPagamento.create', compact('lista'));
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'nome_conta' => 'required',
            'nome_instituicao' => 'required',
            'usuarios_permitidos' => 'required',
            'chave_acesso' => 'required',
            'site_acesso' => 'required',
        ]);

        $data = $request->all();
        $data['usuarios_permitidos'] = json_encode($data['usuarios_permitidos']);

        instituicao_pagamento::create($data);

        return Response::json(['status' => 1, 'reload' => true]);
    }

    public function edit($id)
    {
        //dd($request->all());
        $instituicao = instituicao_pagamento::findOrFail($id);
        $instituicao->usuarios_permitidos = json_decode($instituicao->usuarios_permitidos, true);
        $lista1 = DB::table('users')->where('active', 1)->lists('email', 'id');
        $lista = DB::table('users')->where('active', 1)->whereIn('id', $instituicao->usuarios_permitidos)->lists('email', 'id');
        $instituicao->lista = $lista;

        if (! empty($lista)) {
            $instituicao->users = array_replace($lista1, $lista);
        } else {
            $instituicao->users = $lista1;
        }

        return view('admin::InstituicaoPagamento.edit', compact('instituicao'));
    }

    public function update(Request $request)
    {
        $instituicao = instituicao_pagamento::findOrFail($request->input('id'));

        //dd($instituicao);

        $this->validate($request, [
            'nome_conta' => 'required',
            'nome_instituicao' => 'required',
            'usuarios_permitidos' => 'required',
            'chave_acesso' => 'required',
            'site_acesso' => 'required',
        ]);

        $data = $request->all();
        $data['usuarios_permitidos'] = json_encode($data['usuarios_permitidos']);

        $instituicao->update($data);

        return Response::json(['status' => 1, 'reload' => true]);
    }

    public function destroy($id)
    {
        $instituicao = instituicao_pagamento::findOrFail($id);
        $instituicao->delete();

        //return Response::json(['status' => 1, 'reload' => true]);
    }
}
