<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateStateType extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'name'
    ];

    public function affiliate_states(){
        return $this->hasMany(AffiliateState::class);
    }
}
