<?php

namespace App;

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
}
