<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\Repositories\DeviceRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Input;
use Tobuli\Entities\Chat;
use Tobuli\Entities\ChatMessage;
use Tobuli\Entities\Device;
use Tobuli\Entities\TraccarDevice;
use Tobuli\Entities\User;

use App\Exceptions\ResourseNotFoundException;
use App\Exceptions\PermissionException;
use Tobuli\Exceptions\ValidationException;

class ChatController extends Controller {

    public function index()
    {
        $this->checkException('chats', 'view');

        $searchData = Input::all();
        $searchData['filter']['traccar_device_id'] = TraccarDevice::where('protocol','osmand')->get()->pluck('id','id')->toArray();
        $chattableObjects = DeviceRepo::searchAndPaginateNew($searchData, 'name', 'asc', 10, [$this->user->id]);

        return view('front::Chat.index')->with(compact('chattableObjects'));
    }

    public function searchParticipant()
    {
        $this->checkException('chats', 'view');

        $searchData = Input::all();
        $searchData['filter']['traccar_device_id'] = TraccarDevice::where('protocol','osmand')->get()->pluck('id','id')->toArray();
        $chattableObjects = DeviceRepo::searchAndPaginateNew($searchData, 'name', 'asc', 10, [$this->user->id]);

        return view('front::Chat.partials.table')->with(compact('chattableObjects'));
    }

    public function getChat($chatId)
    {
        $chat = Chat::with(['participants'])->find($chatId);

        $this->checkException('chats', 'show', $chat);

        return view('front::Chat.partials.conversation')
            ->with([
                'chat' => $chat,
                'messages' => $chat->getLastMessages(),
            ]);
    }

    public function getMessages($chatId)
    {
        $chat = Chat::find($chatId);

        $this->checkException('chats', 'show', $chat);

        $messages = $chat->getLastMessages();

        return response()->json(array_merge(['status' => 1], $messages->toArray()));
    }
    
    public function initChat($chatableId, $type = 'device')
    {
        $this->checkException('chats', 'store');

        switch ($type) {
            case 'device':
                $device = Device::find($chatableId);
                $chat = Chat::getRoomByDevice($device);
                $chat->addParticipant($this->user);

                break;
            case 'user':
                $participants = new Collection();
                $participants->push(User::find($chatableId));
                $participants->push($this->user);

                $chat = Chat::getRoom($participants);

                break;
            default:
                throw new \Exception("Type '$type' not supported");
        }

        return view('front::Chat.partials.conversation')->with([
                'chat' => $chat,
                'messages' => $chat->getLastMessages()->setPath(route('chat.messages', $chat->id))
            ]);
    }

    public function createMessage($chatId) {
        if (empty($this->data['message']))
            throw new ValidationException(['message' => trans('validation.attributes.message')]);

        $chat = Chat::find($chatId);

        $this->checkException('chats', 'update', $chat);

        $message = new ChatMessage();
        $message
            ->setTo(null, $chat)
            ->setFrom($this->user)
            ->setContent($this->data['message'])->send();

        return response()->json(['status' => 1]);
    }

}