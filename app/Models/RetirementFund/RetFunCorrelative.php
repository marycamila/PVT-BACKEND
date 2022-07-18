<?php

namespace App\Models\RetirementFund;

use App\Models\Admin\User;
use App\Models\Workflow\WfState;
use App\Models\RetirementFund\RetirementFund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunCorrelative extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'wf_state_id',
        'retirement_fund_id',
        'code',
        'user_id',
        'date',
        'note'
    ];

    public function wf_state()
    {
        return $this->belongsTo(WfState::class,'wf_state_id');
    }
    public function retirement_fund()
    {
        return $this->belongsTo(RetirementFund::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
