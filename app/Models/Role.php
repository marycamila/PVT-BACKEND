<?php

namespace App\Models;

use Laratrust\Models\LaratrustRole;

class Role extends LaratrustRole
{
    protected $hidden = ['pivot'];
    public $guarded = ['id'];
    protected $fillable = ['module_id', 'name', 'display_name', 'sequence_number'];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_permissions')->withTimestamps();
    }

    public function records()
    {
        return $this->morphMany(Record::class, 'recordable');
    }
}
