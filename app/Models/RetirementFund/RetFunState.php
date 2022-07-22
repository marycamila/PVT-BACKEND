<?php

namespace App\Models\RetirementFund;

use App\Models\RetirementFund\RetirementFund;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetFunState extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'name'
    ];

    public function retirement_funds()
    {
        return $this->hasMany(RetirementFund::class);
    }
}
