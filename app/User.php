<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laratrust\Traits\LaratrustUserTrait;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','token','refresh_token'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','token','refresh_token'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    public function posts() {
        return $this->hasMany(Posts::class);
    }
    public function checkToken($token){
        $token = request()->header('token');
        if (!$token){
            return false;
        }else {
            $check = $this::where('token',$token)->first();
            if ($check){
                return true ;
            }
        }
    }

    public function LayId($token){
        $token = request()->header('token');
        $user = $this::where('token',$token)->first();
        return $user->id ;
    }
}
