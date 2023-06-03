<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class instituicao_pagamento extends Model
{
    protected $table = 'instituicao_pagamento';

    protected $fillable = [
        'nome_conta',
        'nome_instituicao',
        'usuarios_permitidos',
        'chave_acesso',
        'site_acesso',
    ];
}
