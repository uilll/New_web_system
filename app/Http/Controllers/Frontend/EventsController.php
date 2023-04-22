<?php namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\EventModalHelper;

use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller {

    public function index() {
        $search = array_key_exists('search', $this->data) ? $this->data['search'] : '';
        $device_id = array_key_exists('device_id', $this->data) ? $this->data['device_id'] : null;

        $events = EventModalHelper::search($search, $device_id);
        if (array_key_exists('data', $events)) {
            foreach ($events['data'] as &$event) {
                if (empty($event))
                    continue;

                $event['message'] = parseEventMessage($event['message'], $event['type']);
            }
        }

        return !$this->api ? view('front::Events.index')->with(['events' => $events]) : ['status' => 1, 'items' => $events];
    }

    public function doDestroy() {
        return view('front::Events.destroy');
    }

    public function destroy() {
        EventModalHelper::destroy();

        return ['status' => 1];
    }

    public function disable_push(){
        $item = UserRepo::find(Auth::User()->id);
        $flag = $item->push_notification;
        $flag = !$flag;
        DB::table('users')->where(['id' => Auth::User()->id])->update(['push_notification' => $flag]);
        if($flag){
            $status = "Habilitadas.";
            $status2 = "irá";
            $icon = "fa-bell-o";
        }
        else{
            $status = "Desabilitadas.";
            $status2 = "não irá";
            $icon = "fa-bell-slash-o";
        }

        $title = $status;
            $body = "As notificações foram ".$status." Você ".$status2." receber as notificações no seu celular.";
        
		return view('front::Interaction_central.alert')->with(compact('title', 'body', 'icon'));
    }
}