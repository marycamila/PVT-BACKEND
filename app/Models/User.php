<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use LaratrustUserTrait;
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'password',
        'active',
        'position',
        'city_id',
        'phone'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'status',
        'remember_token',
    ];

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    public function getModulesAttribute()
    {
        return $this->roles()->pluck('module_id')->unique()->toArray();
    }

    public function rolesByModule($id_module)
    {
        return $this->roles()->where('module_id',$id_module)->get();
    }

    public function setFirstNameAttribute($value)
    {
        $this->attributes['first_name'] = trim(mb_strtoupper($value));
    }

    public function setLastNameAttribute($value)
    {
        $this->attributes['last_name'] = trim(mb_strtoupper($value));
    }


    public function setUsernameAttribute($value)
    {
        $this->attributes['username'] = trim($value);
    }

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::make($value);
    }
}
