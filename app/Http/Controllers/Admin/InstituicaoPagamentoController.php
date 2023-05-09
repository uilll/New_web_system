<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\instituicao_pagamento;
use Illuminate\Support\Facades\DB;

class InstituicaoPagamentoController extends BaseController
{
    public function index()
    {
        $instituicoes = instituicao_pagamento::all();
        foreach ($instituicoes as $instituicao){
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

        return Response::json(['status' => 1]);
    }

    public function edit(instituicao_pagamento $instituicao)
    {
        $lista1 = DB::table('users')->where('active', 1)->lists('email', 'id'); 
        $lista = DB::table('users')->where('active', 1)->whereIn('id', json_decode($instituicao->usuarios_permitidos, true))->lists('email', 'id');
        $instituicao->lista = $lista;
        
        if(!empty($lista))
        {

            $instituicao->users = array_replace($lista1, $lista);
        }
        else{
            $instituicao->users = $lista1;
        }
        return view('admin::InstituicaoPagamento.edit', compact('instituicao'));
    }

    public function update(Request $request, instituicao_pagamento $instituicao)
    {
        $validatedData = $request->validate([
            'nome_conta' => 'required',
            'nome_instituicao' => 'required',
            'usuarios_permitidos' => 'required',
            'chave_acesso' => 'required',
            'site_acesso' => 'required',
        ]);

        $instituicao->update($validatedData);

        return redirect()->route('instituicaopagamento.index')
            ->with('success', 'Instituição de pagamento atualizada com sucesso.');
    }

    public function destroy(instituicao_pagamento $instituicao)
    {
        $instituicao->delete();

        return redirect()->route('instituicaopagamento.index')
            ->with('success', 'Instituição de pagamento excluída com sucesso.');
    }
}