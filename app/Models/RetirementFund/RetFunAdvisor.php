<?php

namespace App\Models\RetirementFund;

use App\Models\Affiliate\Kinship;
use App\Models\City;
use App\Models\RetirementFund\RetFunBeneficiary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunAdvisor extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'direct_contribution_id',
        'id bigserial',
        'city_identity_card_id',
        'kinship_id',
        'identity_card',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'birth_date',
        'type',
        'name_court',
        'resolution_number',
        'resolution_date',
        'phone_number',
        'cell_phone_number',
        'gender'
    ];

    public function city_identity_card()
    {
        return $this->belongsTo(City::class,'city_identity_card_id','id');
    }
    
    public function kinship()
    {
        return $this->belongsTo(Kinship::class);
    }

    public function ret_fun_beneficiaries()
    {
        return $this->belongsToMany(RetFunBeneficiary::class,'ret_fun_advisor_beneficiary','ret_fun_advisor_id','ret_fun_beneficiary_id');
    }
}
