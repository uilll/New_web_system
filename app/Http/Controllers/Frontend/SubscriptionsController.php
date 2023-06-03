<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use DateTime;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Tobuli\Repositories\BillingPlan\BillingPlanRepositoryInterface as BillingPlan;

class SubscriptionsController extends Controller
{
    public function index()
    {
        $dStart = new DateTime(date('Y-m-d H:i:s'));
        $dEnd = new DateTime(Auth::User()->subscription_expiration);
        $dDiff = $dStart->diff($dEnd);
        $days_left = $dDiff->days;

        return View::make('front::Subscriptions.index')->with(compact('item', 'days_left'));
    }

    public function languages()
    {
        $languages = array_sort(settings('languages'), function ($language) {
            return $language['title'];
        });

        $languages = array_filter($languages, function ($language) {
            return $language['active'];
        });

        return View::make('front::Subscriptions.languages', compact('languages'));
    }

    public function renew(BillingPlan $billingPlanRepo)
    {
        if (settings('main_settings.enable_plans') != 1) {
            return Redirect::route('home');
        }

        $permissions = Config::get('tobuli.permissions');

        $plans = $billingPlanRepo->getWhere(['visible' => true], 'objects', 'asc');

        return view('front::Subscriptions.renew')->with(compact('plans', 'permissions'));
    }
}
