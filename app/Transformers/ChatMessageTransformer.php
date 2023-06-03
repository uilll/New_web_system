<?php

namespace App\Transformers;

use League\Fractal\TransformerAbstract;
use Tobuli\Entities\ChatMessage;

class ChatMessageTransformer extends TransformerAbstract
{
    public function transform(ChatMessage $entity)
    {
        return [
            'id' => $entity->id,
            'content' => $entity->content,
            'type' => $entity->type,
            'sender_id' => $entity->sender_id,
            'sender_name' => $entity->senderName,
            'chattable_id' => $entity->chattable_id,
        ];
    }
}
