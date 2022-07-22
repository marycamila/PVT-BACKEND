<?php

namespace App\Models\RetirementFund;

use App\Models\RetirementFund\RetirementFund;
use App\Models\Procedure\ProcedureRequirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunSubmittedDocument extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'retirement_fund_id',
        'procedure_requirement_id',
        'is_valid',
        'comment'
    ];

    public function retirement_fund()
    {
        return $this->belongsTo(RetirementFund::class,'retirement_fund_id','id');
    }

    public function procedure_requirement()
    {
        return $this->belongsTo(ProcedureRequirement::class,'procedure_requirement_id','id');
    }
}
