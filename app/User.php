<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Carbon\Carbon;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'goodreads_id',
        'last_access'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function updateLastAccess(){
        $this->last_access = Carbon::now();
        $this->save();
    }

    public function isUserDataOutdated(){
        $lastAccess = $this->last_access; // last access by user
        $hoursSinceLastAccess = Carbon::now()->diffInHours($lastAccess);
        if ($hoursSinceLastAccess > config('goodreads.refresh_data_rate')){
            return true;
        }
        return false;
    }
}
