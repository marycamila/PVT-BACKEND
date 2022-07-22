<?php

namespace App\Models\QuotaAidMortuary;

use App\Models\City;
use App\Models\Affiliate\Kinship;
use App\Models\QuotaAidMortuary\QuotaAidBeneficiary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaAidAdvisor extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'city_identity_card_id',
        'kinship_id',
        'identity_card',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'birth_date',
        'gender', 
        'type',
        'name_court',
        'resolution_number', 
        'resolution_date',
        'phone_number',
        'cel_phone_number',
    ];

    public function city_identity_card()    
    {
        return $this->belongsTo(City::class, 'city_identity_card_id');
    }
    public function kinship()
    {
        return $this->belongsTo(Kinship::class);
    }
    public function quota_aid_beneficiary()
    {
        return $this->belongsToMany(QuotaAidBeneficiary::class, 'quota_aid_advisor_beneficiary', 'quota_aid_beneficiary_id', 'quota_aid_advisor_id');
    }
}
