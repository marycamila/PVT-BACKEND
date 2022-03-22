<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User;

class AidContribution extends Model
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
        'valid',
        'affiliate_rent_class',
        'aid_contributionable_type',
        'aid_contributionable_id'

    ];
    public function affiliate()
    {
        //return $this->belongsTo(Affiliate::class);
    }

    public function user(){
        return $this->hasOne(User::class,'id','id');
    }
    public function aid_contributionable()
    {
        return $this->morphTo();
    }
}
