<?php namespace Tobuli\Repositories\DeviceIcon;

use Tobuli\Entities\DeviceIcon as Entity;
use Tobuli\Repositories\EloquentRepository;

class EloquentDeviceIconRepository extends EloquentRepository implements DeviceIconRepositoryInterface {

    public function __construct( Entity $entity )
    {
        $this->entity = $entity;
    }

    public function whereNotInFirst($ids)
    {
        return $this->entity->whereNotIn('id', $ids)->first();
    }

    public function all()
    {
        return $this->entity->orderBy('order', 'desc')->orderBy('id', 'asc')->get();
    }

    public function getMyIcons($user_id) {
        return $this->entity->whereNull('user_id')->orWhere(['user_id' => $user_id])->orderBy('order', 'desc')->orderBy('id', 'asc')->get();
    }

    public function searchAndPaginate(array $data, $sort_by, $sort = 'asc', $limit = 10)
    {
        $data = $this->generateSearchData($data);
        $sort = array_merge([
            'sort' => $sort,
            'sort_by' => $sort_by
        ], $data['sorting']);

        $items = $this->entity
            ->where('id', '<>', 0)
            ->orderBy($sort['sort_by'], $sort['sort'])
            ->where(function ($query) use ($data) {
                if (!empty($data['search_phrase'])) {
                    foreach ($this->searchable as $column) {
                        $query->orWhere($column, 'like', '%' . $data['search_phrase'] . '%');
                    }
                }

                if (count($data['filter'])) {
                    foreach ($data['filter'] as $key=>$value) {
                        $query->where($key, $value);
                    }
                }
            })
            ->paginate($limit);

        $items->sorting = $sort;

        return $items;
    }

}