<?php

namespace App\Models\RetirementFund;

use App\Models\Admin\User;
use App\Models\RetirementFund\RetirementFund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunRecord extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'ret_fun_id',
        'message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function retirement_fund()
    {
        return $this->belongsTo(RetirementFund::class,'ret_fun_id','id');
    }
   
}
