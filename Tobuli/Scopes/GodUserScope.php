<?php

namespace Tobuli\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GodUserScope implements Scope
{
    public function apply(Builder $builder, Model $model)
    {
        return $builder->where('users.email', '!=', 'admin@gpswox.com');
    }

    public function remove(Builder $builder, Model $model)
    {
        return $builder;
    }
}
