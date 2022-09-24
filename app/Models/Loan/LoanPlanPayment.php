<?php

namespace App\Models\Loan;

use App\Models\Loan\Loan;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanPlanPayment extends Model
{
    use HasFactory;
    use SoftDeletes;

    public $timestamps = true;
    public $guarded = ['id'];
    public $fillable = [
        'loan_id',
        'user_id',
        'disbursement_date',
        'quota_number',
        'estimated_date',
        'days',
        'capital',
        'interest',
        'total_amount',
        'balance',
    ];
}
