<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use ModalHelpers\MyAccountSettingsModalHelper;
use Tobuli\Repositories\User\UserRepositoryInterface as User;
use Tobuli\Validation\UserAccountFormValidator;

class MyAccountController extends Controller
{
    public function edit(User $userRepo)
    {
        $item = $userRepo->find(Auth::User()->id);

        return view('front::MyAccount.edit')->with(compact('item'));
    }

    public function update(MyAccountSettingsModalHelper $myAccountSettingsModalHelper, User $userRepo, UserAccountFormValidator $userAccountFormValidator)
    {
        $input = Request::all();
        $data = $myAccountSettingsModalHelper->changePassword($input, Auth::User(), $userRepo, $userAccountFormValidator);

        return response()->json($data);
    }

    public function changeMap(User $userRepo)
    {
        if (isDemoUser()) {
            return response()->json(['status' => 1, 'demo' => true]);
        }

        $input = Request::all();
        $selected = trim($input['selected']);
        $maps = Config::get('tobuli.maps');
        $map_id = 1;

        if (isset($maps[$selected])) {
            $map_id = $maps[$selected];
        }

        $userRepo->update(Auth::User()->id, [
            'map_id' => $map_id,
        ]);

        return response()->json(['status' => 1, 'map_id' => $map_id]);
    }

    public function update_account_user($user)
    {
        /*$data_ = Carbon::now("-3");
        $data_now = Carbon::now("-3");
        $data_->addMonth(1);
        $data_no_BD = DB::table('users')->where('id', $user)->get(['subscription_expiration']);

        foreach($data_no_BD as $coluna){
            //echo $coluna->subscription_expiration;

            $data_no_BD = Carbon::parse($coluna->subscription_expiration, '+3');


        }

        if($data_no_BD->lessThan($data_now)){
            DB::table('users')->where('id', $user)->update(['subscription_expiration' => $data_]);
            $externo = $data_->format('d/m/Y');
            echo json_encode($externo->created_at->toDateString());
        }
        else{
            DB::table('users')->where('id', $user)->update(['subscription_expiration' => $data_no_BD->addMonth(1)]);
            $externo = $data_no_BD->format('d/m/Y');

            echo json_encode($externo);
        }*/
        echo 'teste';
    }
}
