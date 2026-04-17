<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'mobile', 'profile_id', 'balance_id', 'password_changed_at'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function member()
    {
        return $this->hasOne('App\Models\Member');
    }

    public function balance()
    {
        return $this->belongsTo('App\Models\Balance');
    }

    public function status()
    {
        return $this->belongsTo('App\Models\Status');
    }

    public function profile()
    {
        return $this->belongsTo('App\Models\Profile');
    }

    public function role()
    {
        return $this->belongsTo('App\Models\Role');
    }

    public function getFullNameAttribute()
    {
        return ucfirst($this->name) . ' ' . $this->mobile;
    }

    public function company()
    {
        return $this->belongsTo('App\Models\Company');
    }
}
