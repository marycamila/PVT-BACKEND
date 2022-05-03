<?php

namespace App\Models\Admin;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Traits\LaratrustUserTrait;
use Illuminate\Support\Facades\Hash;
use App\Models\Contribution\AidContribution;
use App\Models\Contribution\AidReimbursement;
use App\Models\Contribution\Contribution;
use App\Models\Contribution\ContributionProcess;
use App\Models\Contribution\ContributionRate;
use App\Models\Contribution\Reimbursement;
use App\Models\Procedure\ProcedureRecord;
use App\Models\Workflow\SequencesRecord;
use App\Models\Workflow\WfRecord;

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
    
    public function contribution_process()
    {
        return $this->hasMany(ContributionProcess::class);
    }
    public function aid_reimbursements()
    {
        return $this->hasMany(AidReimbursement::class);
    }
    public function aid_contributions()
    {
        return $this->hasMany(AidContribution::class);
    }
    public function procedure_records()
	{
		return $this->hasMany(ProcedureRecord::class);
    }
    public function sequences_records()
	{
		return $this->hasMany(SequencesRecord::class);
    }
    public function wf_records()
	{
		return $this->hasMany(WfRecord::class);
    }

}
