<?php

namespace App\Models;

use App\Models\Affiliate\Affiliate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffiliateToken extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'affiliate_id',
        'api_token',
        'device_id',
        'firebase_token'
    ];
    public function affiliate(){
        return $this->belongsTo(Affiliate::class);
    }
}
