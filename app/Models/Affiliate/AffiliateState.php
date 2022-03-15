<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateState extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'affiliate_state_type_id',
        'name'
    ];

    public function affiliates(){
        return $this->hasMany(Affiliate::class);
    }

    public function affiliate_state_type(){
        return $this->belongsTo(AffiliateStateType::class);
    }
}
