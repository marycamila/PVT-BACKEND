<?php

namespace App\Models\RetirementFund;

use App\Models\RetirementFund\DirectContribution;
use App\Models\Procedure\ProcedureRequirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectContributionSubmittedDocument extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'direct_contribution_id',
        'procedure_requirement_id',
        'reception_date',
        'comment',
        'is_valid'
    ];

    public function direct_contribution()
    {
        return $this->belongsTo(DirectContribution::class);
    } 
    public function procedure_requirement()
    {
        return $this->belongsTo(ProcedureRequirement::class);
    } 
}
