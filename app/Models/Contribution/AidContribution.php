<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\User;
use App\Models\Affiliate\Affiliate;

class AidContribution extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'affiliate_id',
        'month_year',
        'type',
        'quotable',
        'rent',
        'dignity_rent',
        'interest',
        'total',
        'created_at',
        'updated_at',
        'deleted_at',
        'affiliate_contribution',
        'mortuary_aid',
        'valid'
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
