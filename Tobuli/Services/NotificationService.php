<?php
/**
 * Created by PhpStorm.
 * User: antanas
 * Date: 18.2.14
 * Time: 15.58
 */

namespace Tobuli\Services;


use App\Notifications\PopupNotification;
use Facades\Repositories\PopupRepo;
use Facades\Repositories\UserRepo;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Notification;
use Tobuli\Entities\Popup;
use Tobuli\Entities\PopupRule;
use Tobuli\Popups\Rules\BaseRule;
use Tobuli\Popups\Rules\BillingPlan;
use Tobuli\Popups\Rules\DemoUser;
use Tobuli\Popups\Rules\SubscriptionEnding;
use Tobuli\Repositories\User\UserRepositoryInterface;

class NotificationService
{

    /**
     * @var UserRepo
     */
    private $userRepo;

    /**
     * Available rules for user notifications
     *
     * @var array
     */
    public static $ruleCollection = [
        BillingPlan::class,
        DemoUser::class,
        SubscriptionEnding::class
    ];

    public function __construct(UserRepositoryInterface $userRepo) {
        $this->userRepo = $userRepo;
    }


    public function save($input) {

        $popup = PopupRepo::first(['id' => $input['id']]);

        if ( ! $popup) {
            $popup = new Popup();
        }

        try {
            $popup->active = (isset($input['active']) ? true : false);
            $popup->title = $input['title'];
            $popup->content = $input['content'];
            $popup->position = $input['position'];
            $popup->show_every_days = $input['show_every_days'];

            $popup->save();

            $popup->rules()->delete();

            $rules = $input['rules'];

            foreach ($rules as $ruleName => $values) {
                if ( ! isset($values['is_active']))
                    continue;

                unset($values['is_active']);

                foreach ($values as $key => $value) {
                    $rule = PopupRule::firstOrNew(['field_name' => $key, 'popup_id' => $popup->id, 'rule_name' => (string) $ruleName]);

                    $rule->field_value = $value;

                    $rule->save();

                    $popup->rules()->save($rule);
                }
            }

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    public function check($user)
    {
        foreach (Popup::where('active', '=', true)->with('rules')->get() as $popup) {
            if ( ! $this->checkRules($popup, $user)) {
                continue;
            }

            $popup = $this->applyOnContent($popup, $user);

            $this->sendNotification($popup, $user);
        }

        return true;
    }


    public function checkRules(Popup $popup, $user) {
        foreach ($popup->rules as $ruleContent) {

            $rule = BaseRule::load($ruleContent, $user);

            if ( ! $rule)
                continue;

            if ( ! $rule->doesApply()) {
                return false;
            }
        }

        return true;
    }

    public function applyOnContent(Popup $popup, $user)
    {
        foreach ($popup->rules as $ruleContent)
        {
            $rule = BaseRule::load($ruleContent, $user);
            if ( ! $rule)
                continue;

            $popup->title   = $rule->processShortcodes($popup->title);
            $popup->content = $rule->processShortcodes($popup->content);
        }

        return $popup;
    }


    public function sendNotification(Popup $popup, $user)
    {
        if ( ! $user)  return false;

        $exists = DatabaseNotification::where('data', '=', $popup->toJson())
            ->where('notifiable_id', '=', $user->id)
            ->where('type', '=', PopupNotification::class)
            ->where('read_at',  '>', date("Y-m-d H:i:s", strtotime("-".$popup->show_every_days . ' days')))
            ->first();

        if ($exists) { return false; }

        $notification = new PopupNotification($popup);

        try {
            Notification::send($user, $notification);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }


}