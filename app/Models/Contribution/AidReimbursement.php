<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\User;
use App\Models\Affiliate\Affiliate;

class AidReimbursement extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'affiliate_id',
        'user_id', 
        'month_year', 
        'quotable', 
        'rent',
        'dignity_rent',
        'interest',
        'total',
        'created_at',
        'updated_at',
        'deleted_at',
        'valid',
        'mortuary_aid'
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
