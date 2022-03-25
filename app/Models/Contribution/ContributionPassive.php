<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;
use App\Models\Contribution\PayrollSenasir;
use App\Models\Contribution\ContributionPassive;

class ContributionPassive extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'affiliate_id',
        'month_year',
        'quotable',
        'rent',
        'dignity_rent',
        'interest',
        'total',
        'affiliate_contribution',
        'mortuary_aid',
        'is_valid',
        'affiliate_rent_class',
        'contributionable_type',
        'contributionable_id'

    ];
    public function affiliate()
    {
        //return $this->belongsTo(Affiliate::class);
    }

    public function user(){
        return $this->hasOne(User::class,'id','user_id');
    }
    public function contributionable()
    {
        return $this->morphTo();
    }

    public static function data_period_senasir($month_year)
    {
        $data = collect([]);
        $exists_data = true;
        $contribution =  ContributionPassive::whereMonth_year($month_year)->whereContributionable_type('payroll_senasirs')->count();
        if($contribution == 0) $exists_data = false;

        $data['exist_data'] = $exists_data;
        $data['count_data'] = $contribution;

        return  $data;
    }

    public static function sum_total_senasir($month_year)
    {
        $contribution =  ContributionPassive::whereMonth_year($month_year)->whereContributionable_type('payroll_senasirs')->sum('total');
        return $contribution;
    }
}
