<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;

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
        'cyti_id',
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

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    /*protected $casts = [
        'email_verified_at' => 'datetime',
    ];*/

    // full name de usuario
    public function getFullNameAttribute()
    {
        return mb_strtoupper(preg_replace('/[[:blank:]]+/', ' ', join(' ', [$this->first_name, $this->last_name])));
    }
    // relacion con la tabla roles
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /*public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'user_permissions');
    }*/
    //modulos del usuario
    public function getModulesAttribute()
    {
        return $this->roles()->pluck('module_id')->unique()->toArray();
    }
    //roles por modulo del usuario
    public function rolesByModule($id_module)
    {
        return $this->roles()->where('module_id',$id_module)->get();
    }
}
