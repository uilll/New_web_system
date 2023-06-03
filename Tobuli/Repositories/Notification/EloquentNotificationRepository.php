<?php

namespace Tobuli\Repositories\Notification;

use Tobuli\Entities\Popup as Entity;
use Tobuli\Repositories\EloquentRepository;

class EloquentNotificationRepository extends EloquentRepository implements NotificationRepositoryInterface
{
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
        $this->searchable = [
            'title',
        ];
    }
}
