<?php

namespace App\Http\Controllers\Frontend\Tracker;

use App\Transformers\ChatMessageTransformer;
use App\Transformers\ChatTransformer;
use FractalTransformer;
use Tobuli\Entities\Chat;
use Tobuli\Entities\ChatMessage;
use Tobuli\Exceptions\ValidationException;
use Validator;

class ChatController extends ApiController
{
    public function initChat()
    {
        $chat = Chat::getRoomByDevice($this->deviceInstance);

        return response()->json(array_merge(
            ['status' => 1],
            FractalTransformer::item($chat, ChatTransformer::class)->toArray()
        ));
    }

    public function getChattableObjects()
    {
        return response()->json(['status' => 1, 'data' => $this->deviceInstance->chatableObjects()]);
    }

    public function getMessages()
    {
        $chat = Chat::getRoomByDevice($this->deviceInstance);

        return response()->json(array_merge(
            ['status' => 1],
            FractalTransformer::paginate($chat->getLastMessages(), ChatMessageTransformer::class)->toArray()
        ));
    }

    public function createMessage()
    {
        $validator = Validator::make(request()->all(), [
            'message' => 'required',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator->errors());
        }

        $messageContent = request()->get('message', null);

        $message = new ChatMessage();

        $chat = Chat::getRoomByDevice($this->deviceInstance);

        $message->setTo(null, $chat)->setFrom($this->deviceInstance)->setContent($messageContent)->send();

        return response()->json(['status' => 1]);
    }
}
