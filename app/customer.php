<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class customer extends Model
{
    //
    protected $fillable = [
        'active',
        'name',
        'in_debt',
        'cpf_cnpj',
        'address',
        'city',
        'contact',
        'users_passwords',
        'obs',
        'manager_id',
        'all_users',
        'created_at',
        'id_app_ext',
        'updated_at',
    ];
}
