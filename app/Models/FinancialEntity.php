<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Affiliate\Affiliate;
use App\Models\Loan\Loan;

class FinancialEntity extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];

    public function affiliates()
    {
        return $this->hasMany(Affiliate::class);
    }
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}
