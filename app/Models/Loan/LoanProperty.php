<?php

namespace App\Models\Loan;

use App\Models\Loan\Loan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanProperty extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    public $fillable = [
        'land_lot_number',
        'neighborhood_unit',
        'location',
        'surface',
        'measurement',
        'cadastral_code',
        'limit',
        'public_deed_number',
        'lawyer',
        'registration_number',
        'real_folio_number',
        'public_deed_date',
        'net_realizable_value',
        'commercial_value',
        'rescue_value',
        'real_city_id'
    ];

    public function loans()
    {
        return $this->hasMany(Loan::class,'property_id','id');
    }
}
