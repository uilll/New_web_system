<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Facades\ModalHelpers\RegistrationModalHelper;

class RegistrationController extends Controller
{
    public function create()
    {
        return view('front::Registration.create');
    }

    public function store()
    {
        $data = RegistrationModalHelper::create();

        if (! $this->api) {
            if ($data['status']) {
                return redirect()->route('login')->with('success', trans('front.registration_successful'));
            } else {
                return redirect()->route('registration.create')->withInput()->withErrors($data['errors']);
            }
        } else {
            return $data;
        }
    }
}
