<?php

namespace App\Models;

use App\Models\City;
use App\Models\Loan\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PersonalReference extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    public $fillable = [
        'city_identity_card_id',
        'city_birth_id',
        'identity_card',
        'last_name',
        'mothers_last_name',
        'first_name',
        'second_name',
        'surname_husband',
        'civil_status',
        'gender',
        'phone_number',
        'cell_phone_number',
        'address'
    ];

    public function city_identity_card()
    {
        return $this->belongsTo(City::class, 'city_identity_card_id', 'id');
    }
    public function city_birth()
    {
        return $this->belongsTo(City::class, 'city_birth_id', 'id');
    }
    public function loans()
    {
        return $this->belongsToMany(Loan::class, 'loan_persons')->withPivot(['cosigner']);
    }
}
