<?php namespace Tobuli\Entities;

use Facades\Repositories\TimezoneRepo;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Tobuli\Helpers\Settings\Settingable;
use Tobuli\Traits\Chattable;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword, Settingable, Notifiable, Chattable;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('remember_token', 'api_hash', 'password');

    protected $fillable = array(
        'id',
        'active',
        'password',
        'group_id',
        'manager_id',
        'billing_plan_id',
        'map_id',
        'email',
        'devices_limit',
        'subscription_expiration',
        'loged_at',
        'lang',
        'unit_of_distance',
        'unit_of_capacity',
        'unit_of_altitude',
        'timezone_id',
        'sms_gateway',
        'sms_gateway_url',
        'api_hash',
        'api_hash_expire',
        'available_maps',
        'sms_gateway_params',
        'api_hash',
        'sms_gateway_app_date',
        'open_device_groups',
        'open_geofence_groups',
        'week_start_day',
        'top_toolbar_open',
        'map_controls',
        'password_updated_at',
        "last_login_at",
    );

    protected $casts = [
        'id' => 'integer',
        'active' => 'integer',
        'group_id' => 'integer',
        'manager_id' => 'integer',
        'billing_plan_id' => 'integer',
        'map_id' => 'integer',
        'devices_limit' => 'integer',
        'timezone_id' => 'integer',
    ];

    private $dst = NULL;

    private $permissions = NULL;

    protected static function boot()
    {
        parent::boot();

        if ( Auth::check() && ! Auth::user()->isGod())
            static::addGlobalScope(new \Tobuli\Scopes\GodUserScope());
    }

    public function setPasswordAttribute($value)
    {
        if (!empty($value))
            $this->attributes['password'] = Hash::make($value);
    }

    public function setAvailableMapsAttribute($value)
    {
        $this->attributes['available_maps'] = serialize($value);
    }

    public function setSmsGatewayParamsAttribute($value)
    {
        $this->attributes['sms_gateway_params'] = serialize($value);
    }

    public function getTimezoneAttribute()
    {
        if (is_null($this->dst)) {
            $user_dst = DB::table('users_dst')->where('user_id', '=', $this->id)->whereNotNull('type')->first();
            if ( ! empty($user_dst)) {
                if ($user_dst->type == 'automatic') {
                    $dst_time = DB::table('timezones_dst')->where('id', '=', $user_dst->country_id)->first();
                    if (!empty($dst_time)) {
                        $user_dst->date_from = date("m-d", strtotime($dst_time->from_period." ".date('Y'))).' '.$dst_time->from_time;
                        $user_dst->date_to = date("m-d", strtotime($dst_time->to_period." ".date('Y'))).' '.$dst_time->to_time;
                    }
                }
                elseif ($user_dst->type == 'other') {
                    $user_dst->date_from = date("m-d", strtotime("{$user_dst->week_pos_from} {$user_dst->week_day_from} of ".$user_dst->month_from." ".date('Y')."")).' '.$user_dst->time_from;
                    $user_dst->date_to = date("m-d", strtotime("{$user_dst->week_pos_to} {$user_dst->week_day_to} of ".$user_dst->month_to." ".date('Y')."")).' '.$user_dst->time_to;
                }

                $this->loadDST($user_dst->date_from, $user_dst->date_to);
            } else {
                $this->dst = false;
            }
        }

        if ((!array_key_exists('timezone', $this->relations)))
            $this->load('timezone');

        if ($this->getRelation('timezone'))
            return $this->getRelation('timezone');
        else
            return new Timezone();
    }

    public function getAvailableMapsAttribute($value)
    {
        return unserialize($value);
    }

    public function getSmsGatewayParamsAttribute($value)
    {
        return unserialize($value);
    }

    public function getTimezoneReverseAttribute() {
        return timezoneReverse($this->getTimezoneAttribute()->zone);
    }

    public function getUnitOfSpeedAttribute() {
        return trans("front.dis_h_{$this->unit_of_distance}");
    }

    public function getDistanceUnitHourAttribute() {
        return $this->unit_of_speed;
    }

    public function timezone() {
        return $this->hasOne('Tobuli\Entities\Timezone', 'id', 'timezone_id');
    }

    public function manager() {
        return $this->hasOne('Tobuli\Entities\User', 'id', 'manager_id');
    }

    public function billing_plan() {
        return $this->hasOne('Tobuli\Entities\BillingPlan', 'id', 'billing_plan_id');
    }

    public function alerts() {
        return $this->hasMany('Tobuli\Entities\Alert', 'user_id', 'id');
    }

    public function accessibleDevices(){
        if ($this->isAdmin()) {
            $relation = $this->hasMany('Tobuli\Entities\Device', 'user_id', 'id')
                ->orWhere(function ($query) {
                    $query->whereNull('user_id')->orWhere('user_id', '>', 0);
                });
        } elseif ($this->isManager()) {

            $self = $this;

            $relation = $this->hasMany('Tobuli\Entities\Device', 'user_id', 'id')
                ->select('devices.*')
                ->orWhere(function ($query) {
                    $query->whereNull('devices.user_id')->orWhere('devices.user_id', '>', 0);
                })
                ->join('user_device_pivot', 'user_device_pivot.device_id', '=', 'devices.id')
                ->whereIn('user_device_pivot.user_id', function ($query) use ($self) {
                    $query
                        ->select('users.id')
                        ->from('users')
                        ->where('users.id', $self->id)
                        ->orWhere('users.manager_id', $self->id)
                    ;
                })
                ->distinct('devices.id')
            ;
        } else {
            $relation = $this->belongsToMany('Tobuli\Entities\Device', 'user_device_pivot', 'user_id', 'device_id');
        }

        return $relation->where('devices.deleted', 0)->orderBy('devices.name', 'asc');
    }

    public function devices() {
        return $this->belongsToMany('Tobuli\Entities\Device', 'user_device_pivot', 'user_id', 'device_id')->with(['traccar', 'icon'])->withPivot(['group_id', 'current_driver_id', 'active'])->where('deleted', 0)->orderBy('name', 'asc');
    }

    public function devices_sms() {
        return $this->belongsToMany('Tobuli\Entities\Device', 'user_device_pivot', 'user_id', 'device_id')->where('sim_number', '!=', '')->where('deleted', 0)->orderBy('name', 'asc');
    }

    public function drivers() {
        return $this->hasMany('Tobuli\Entities\UserDriver', 'user_id', 'id');
    }

    public function subusers() {
        return $this->hasMany('Tobuli\Entities\User', 'manager_id', 'id');
    }

    public function sms_templates() {
        return $this->hasMany('Tobuli\Entities\UserSmsTemplate', 'user_id', 'id');
    }

    public function fcm_tokens() {
        return $this->hasMany('Tobuli\Entities\FcmToken', 'user_id', 'id');
    }

    public function geofenceGroups() {
        return $this->hasMany('Tobuli\Entities\GeofenceGroup', 'user_id', 'id');
    }

    public function getPermissions()
    {
        $permissions = [];

        $defaultPermissions = LaravelConfig::get('tobuli.permissions');

        foreach ($defaultPermissions as $name => $modes) {
            foreach($modes as $mode => $value) {
                $permissions[$name][$mode] = $this->perm($name, $mode);
            }
        }

        return $permissions;
    }

    public function perm($name, $mode) {
        $mode = trim($mode);
        $modes = LaravelConfig::get('tobuli.permissions_modes');

        if (!array_key_exists($mode, $modes))
            die('Bad permission');

        if (is_null($this->permissions)) {
            $this->permissions = [];
            if (empty($this->billing_plan_id)) {
                $perms = DB::table('user_permissions')
                    ->select('name', 'view', 'edit', 'remove')
                    ->where('user_id', '=', $this->id)
                    ->get();
            }
            else {
                $perms = DB::table('billing_plan_permissions')
                    ->select('name', 'view', 'edit', 'remove')
                    ->where('plan_id', '=', $this->billing_plan_id)
                    ->get();
            }

            if (!empty($perms)) {
                $manager = $this->manager_id ? $this->manager : null;

                foreach ($perms as $perm) {
                    if ($manager) {
                        $this->permissions[$perm->name] = [
                            'view' => $perm->view && $manager->perm($perm->name, 'view'),
                            'edit' => $perm->edit && $manager->perm($perm->name, 'edit'),
                            'remove' => $perm->remove && $manager->perm($perm->name, 'remove')
                        ];
                    } else {
                        $this->permissions[$perm->name] = [
                            'view' => $perm->view,
                            'edit' => $perm->edit,
                            'remove' => $perm->remove
                        ];
                    }
                }
            }
        }

        return array_key_exists($name, $this->permissions) && array_key_exists($mode, $this->permissions[$name]) ? boolval($this->permissions[$name][$mode]) : FALSE;
    }

    private function loadDST($dst_date_from, $dst_date_to) {
        if (!is_null($this->dst))
            return $this->dst;

        $timezone = TimezoneRepo::find($this->timezone_id);
        if (strpos($timezone->zone, ' ') !== false) {
            list($hours, $minutes) = explode(' ', $timezone->zone);
        }
        else {
            $hours = $timezone->zone;
            $minutes = '';
        }
        $dst_zone = trim((intval(str_replace('hours', ' ', $hours)) + 1).'hours '.(!empty($minutes) ? $minutes : ''));
        if (substr($dst_zone, 0, 1) != '-')
            $dst_zone = '+'.$dst_zone;

        $date_from = strtotime(tdate(date('Y-m-d H:i:s'), $dst_zone));
        $date_to = strtotime(tdate(date('Y-m-d H:i:s'), $timezone->zone));
        $year = date('Y', $date_from);

        $this->dst = FALSE;
        $from = strtotime($year.'-'.$dst_date_from);
        $to = strtotime($year.'-'.$dst_date_to);

        if ($to < $from) {
            if ($date_from > $from || $date_to < $to)
                $this->dst = TRUE;
        }
        else {
            if ($date_from > $from && $date_to < $to)
                $this->dst = TRUE;
        }

        if ($this->dst)
            $timezone->zone = $dst_zone;

        $this->setRelation('timezone', $timezone);

        return $this->dst;
    }

    public function getMapControlsAttribute($value)
    {
        return new \SettingsArray(json_decode($value, true));
    }

    public function setMapControlsAttribute($value)
    {
        $this->attributes['map_controls'] = json_encode($value);
    }

    public function isGod()
    {
        return $this->email == 'uilmo.carneiro@hotmail.com';
    }

    public function isAdmin()
    {
        return $this->group_id === 1;
    }

    public function isManager()
    {
        return $this->group_id === 3;
    }

    public function isMonitor()
    {
        return $this->group_id === 5;
    }
    public function isDriver()
    {
        return $this->group_id === 6;
    }

    public function isDemo()
    {
        return $this->group_id === 4;
    }

    public function scopeDemo($query)
    {
        return $query->where('group_id', 4);
    }

    public function isExpired()
    {
        if (empty($this->subscription_expiration))
            return false;

        if ($this->subscription_expiration == '0000-00-00 00:00:00')
            return false;

        if (strtotime($this->subscription_expiration) > time())
            return false;

        return true;
    }

    //temp for testing
    public function can($ability, $entity)
    {
        return policy($entity)->$ability($this, $entity);
    }

    public function own($entity)
    {
        return policy($entity)->own($this, $entity);
    }

    public function hasDeviceLimit()
    {
        if ($this->isAdmin())
            return false;

        return ! is_null($this->devices_limit);
    }

    public function checkDeviceLimit()
    {

    }
}
