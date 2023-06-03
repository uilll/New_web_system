<?php

namespace Tobuli\Traits;

trait Searchable
{
    public function scopeSearch($query, $value)
    {
        if (empty($value)) {
            return $query;
        }

        if (empty($this->searchable)) {
            return $query;
        }

        $query->where(function ($query) use ($value) {
            foreach ($this->searchable as $searchable) {
                $parts = explode('.', $searchable);

                if (isset($parts[1])) {
                    $relation = $parts[0];
                    $field = $parts[1];
                } else {
                    $relation = null;
                    $field = $parts[0];
                }
                if ($relation) {
                    $query->orWhereHas($relation, function ($query) use ($field, $value) {
                        $query->where($field, 'like', '%'.$value.'%');
                    });
                } else {
                    $query->orWhere($field, 'like', '%'.$value.'%');
                }
            }
        });

        return $query;
    }
}
