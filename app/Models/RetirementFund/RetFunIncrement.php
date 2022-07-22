<?php

namespace App\Models\RetirementFund;

use App\Models\Admin\Role;
use App\Models\RetirementFund\RetirementFund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunIncrement extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'role_id',
        'retirement_fund_id',
        'number'
    ];

    public function role()  
    {
        return $this->belongsTo(Role::class);
    }
    public function retirement_fund()  
    {
        return $this->belongsTo(RetirementFund::class);
    }
}
