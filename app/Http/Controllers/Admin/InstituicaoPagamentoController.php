<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Models\InstituicaoPagamento;

class InstituicaoPagamentoController extends Controller
{
    public function index()
    {
        $instituicoes = InstituicaoPagamento::all();
        return view('instituicao_pagamento.index', compact('instituicoes'));
    }

    public function create()
    {
        return view('instituicao_pagamento.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'nome_conta' => 'required',
            'nome_instituicao' => 'required',
            'usuarios_permitidos' => 'required',
            'chave_acesso' => 'required',
            'site_acesso' => 'required',
        ]);

        InstituicaoPagamento::create($validatedData);

        return redirect()->route('instituicao_pagamento.index')
            ->with('success', 'Instituição de pagamento criada com sucesso.');
    }

    public function edit(InstituicaoPagamento $instituicao)
    {
        return view('instituicao_pagamento.edit', compact('instituicao'));
    }

    public function update(Request $request, InstituicaoPagamento $instituicao)
    {
        $validatedData = $request->validate([
            'nome_conta' => 'required',
            'nome_instituicao' => 'required',
            'usuarios_permitidos' => 'required',
            'chave_acesso' => 'required',
            'site_acesso' => 'required',
        ]);

        $instituicao->update($validatedData);

        return redirect()->route('instituicao_pagamento.index')
            ->with('success', 'Instituição de pagamento atualizada com sucesso.');
    }

    public function destroy(InstituicaoPagamento $instituicao)
    {
        $instituicao->delete();

        return redirect()->route('instituicao_pagamento.index')
            ->with('success', 'Instituição de pagamento excluída com sucesso.');
    }
}