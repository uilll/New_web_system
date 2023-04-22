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
        'created_at',
        'updated_at' 
    ]; 
}








