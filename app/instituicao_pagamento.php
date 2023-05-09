<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InstituicaoPagamento extends Model
{
    protected $table = 'instituicao_pagamento';

    protected $fillable = [
        'nome_conta', 
        'nome_instituicao', 
        'usuarios_permitidos', 
        'chave_acesso', 
        'site_acesso'
    ];

    protected $casts = [
        'usuarios_permitidos' => 'array'
    ];
}
