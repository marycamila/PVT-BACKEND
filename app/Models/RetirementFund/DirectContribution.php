<?php

namespace App\Models\RetirementFund;

use App\Models\Admin\User;
use App\Models\Affiliate\Affiliate;
use App\Models\City;
use App\Models\Contribution\ContributionProcess;
use App\Models\Procedure\ProcedureModality;
use App\Models\Procedure\ProcedureState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DirectContribution extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'affiliate_id',
        'user_id',
        'city_id',
        'procedure_modality_id',
        'procedure_state_id',
        'commitment_date',
        'document_number',
        'document_date',
        'start_contribution_date',
        'date',
        'code',
        'status'
    ];

    public function contribution_processes()
    {
        return $this->hasMany(ContributionProcess::class);
    }
    public function user()  
    {
        return $this->belongsTo(User::class);
    }
    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
    public function city()
    {
        return $this->belongsTo(City::class);
    }
    /*public function contributor_type()
    {
        return $this->belongsTo(Kinship::class);
    }*/
    public function procedure_state()
    {
        return $this->belongsTo(ProcedureState::class);
    }
    public function procedure_modality()
    {
        return $this->belongsTo(ProcedureModality::class);
    }
}
