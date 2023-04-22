<?php

namespace Tobuli\Scopes;

use Illuminate\Database\Eloquent\ScopeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class GodUserScope implements ScopeInterface
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