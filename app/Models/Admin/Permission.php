<?php

namespace App\Models\Admin;

use Laratrust\Models\LaratrustPermission;

class Permission extends LaratrustPermission
{
    public $timestamps = true;
    protected $hidden = ['pivot'];
    public $guarded = ['id'];
    protected $fillable = ['name', 'display_name', 'description'];

    public function role()
    {
        return $this->hasMany(Role::class, 'role_permissions');
    }

}
