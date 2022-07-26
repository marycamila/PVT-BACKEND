<?php

namespace App\Models\Loan;

use App\Models\Loan\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanBorrower extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'loan_id',
        'degree_id',
        'unity_id',
        'category_id',
        'type_affiliate',
        'unit_police_description',
        'affiliate_state_id',
        'identity_card',
        'city_identity_card_id',
        'city_birth_id',
        'registration',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'gender',
        'civil_status',
        'phone_number',
        'cell_phone_number',
        'address_id',
        'pension_entity_id',
        'payment_percentage',
        'payable_liquid_calculated',
        'bonus_calculated',
        'quota_previous',
        'quota_treat',
        'indebtedness_calculated',
        'indebtedness_calculated_previous',
        'liquid_qualification_calculated',
        'contributionable_ids',
        'contributionable_type',
        'type'
    ]; 

    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }
    public function getFullNameAttribute()
    {
      return rtrim(preg_replace('/[[:blank:]]+/', ' ', join(' ', [$this->first_name, $this->second_name, $this->last_name, $this->mothers_last_name,$this->surname_husband])));
    }
}