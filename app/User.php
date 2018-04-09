<?php

namespace App;

use App\Notifications\MyOwnResetPassword;
use App\Scopes\StatusScope;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'phone'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'status'
    ];

    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(new StatusScope());
    }

    public function stocks()
    {
        return $this->belongsToMany('App\Stock')->withTimestamps();
    }

    public function warningConfigs()
    {
        return $this->hasMany('App\WarningConfig');
    }

    public function warningRecords()
    {
        return $this->hasMany('App\WarningRecord');
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new MyOwnResetPassword($token));
    }

    public function updateUserInfo($data)
    {
        $this->email = $data['email'];
        $this->phone = $data['phone'];
        $this->username = $data['username'];

        return $this->save();
    }

    public function changePassword($data)
    {
        $this->password = bcrypt($data['password']);

        return $this->save();
    }

    public static function bindWeChat($message)
    {
        $userId = substr($message['EventKey'], 8);
        \Log::info($userId);
        \Log::info($message);
        $user = User::find($userId);
        $user->open_id = $message['FromUserName'];

        return $user->save();
    }

    public function routeNotificationForWechat()
    {
        return $this->open_id;
    }
}
