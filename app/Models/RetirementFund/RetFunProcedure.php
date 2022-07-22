<?php

namespace App\Models\RetirementFund;

use App\Models\RetirementFund\RetirementFund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunProcedure extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'annual_yield',
        'administrative_expenses',
        'contributions_number',
        'is_enabled',
        'contribution_regulate_days'
    ];

    public function retirement_funds()
    {
        return $this->hasMany(RetirementFund::class);
    }
}
