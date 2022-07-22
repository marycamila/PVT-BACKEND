<?php

namespace App\Models\QuotaAidMortuary;

use App\Models\City;
use App\Models\QuotaAidMortuary\QuotaAidBeneficiary;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaAidLegalGuardian extends Model
{
    use HasFactory;

    protected $table = "quota_aid_legal_guardians";
    
    public function city_identity_card()    
    {
        return $this->belongsTo(City::class, 'city_identity_card_id');
    }
    public function quota_aid_beneficiary()
    {
        return $this->belongsToMany(QuotaAidBeneficiary::class, 'quota_aid_beneficiary_legal_guardian', 'quota_aid_beneficiary_id', 'quota_aid_legal_guardian_id');
    }
}
