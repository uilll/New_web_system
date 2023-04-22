<?php namespace Tobuli\Repositories\Tasks;

use Tobuli\Entities\Task as Entity;
use Tobuli\Repositories\EloquentRepository;

class EloquentTasksRepository extends EloquentRepository implements TasksRepositoryInterface {

    public function __construct( Entity $entity )
    {
        $this->entity = $entity;
    }

    public function searchAndPaginate(array $data, $sort_by, $sort = 'asc', $limit = 10)
    {
        $data = $this->generateSearchData($data);
        $sort = array_merge([
            'sort' => $sort,
            'sort_by' => $sort_by
        ], $data['sorting']);

        $items = $this->entity
            ->orderBy($sort['sort_by'], $sort['sort'])
            ->where(function ($query) use ($data) {
                if (!empty($data['search_phrase'])) {
                    foreach ($this->searchable as $column) {
                        $query->orWhere($column, 'like', '%' . $data['search_phrase'] . '%');
                    }
                }

                if (count($data['filter'])) {
                    foreach ($data['filter'] as $key=>$value) {
                        switch ($key) {
                            case 'delivery_time_from':
                                $query->where($key, '>', $value);
                                break;
                            case 'delivery_time_to':
                                $query->where($key, '<', $value);
                                break;
                            default:
                                $query->where($key, $value);
                                break;
                        }

                    }
                }
            })
            ->paginate($limit);

        $items->sorting = $sort;

        return $items;
    }

    public function findWithAttributes($id) {
        return Entity::where('id', $id)->with('statuses')->first();
    }
}