<?php

namespace App\Models\RetirementFund;

use App\Models\City;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunLegalGuardian extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'retirement_fund_id',
        'city_identity_card_id',
        'identity_card',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'phone_number',
        'cell_phone_number',
        'number_authority',
        'notary_of_public_faith',
        'notary',
        'gender',
        'date_authority'
    ];

    public function city_identity_card()
    {
        return $this->belongsTo(City::class, 'city_identity_card_id', 'id');
    }
    public function retirement_fund()
    {
        return $this->belongsTo(RetirementFund::class);
    }
}
