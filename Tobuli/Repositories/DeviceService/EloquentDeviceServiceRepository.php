<?php

namespace Tobuli\Repositories\DeviceService;

use Tobuli\Entities\DeviceService as Entity;
use Tobuli\Repositories\EloquentRepository;

class EloquentDeviceServiceRepository extends EloquentRepository implements DeviceServiceRepositoryInterface
{
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->searchable = [];
    }

    public function deleteDeviceWhereNotIn($arr, $device_id, $id = 'id')
    {
        return $this->entity->where('device_id', $device_id)->whereNotIn($id, $arr)->delete();
    }
}
