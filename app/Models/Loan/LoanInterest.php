<?php

namespace App\Models\Loan;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanInterest extends Model
{
    public $timestamps = true;
    protected $fillable = ['procedure_modality_id', 'annual_interest','penal_interest'];
    public $guarded = ['id'];

    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
    public function procedure_modality()
    {
        return $this->belongsTo(ProcedureModality::class);
    }
    public function getMonthlyCurrentInterestAttribute()
    {
        return $this->annual_interest / (100 * 12);
    }

}
