<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\User;
use App\Models\Affiliate\Affiliate;
use App\Models\Affiliate\Category;
use App\Models\Affiliate\Degree;

class Contribution extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'affiliate_id',
        'degree_id',
        'unit_id',
        'breakdown_id',
        'category_id',
        'month_year',
        'type',
        'base_wage',
        'seniority_bonus',
        'study_bonus',
        'position_bonus',
        'border_bonus',
        'east_bonus',
        'public_security_bonus',
        'gain',
        'payable_liquid',
        'quotable',
        'retirement_fund',
        'mortuary_quota',
        'subtotal',
        'interest',
        'total',
        'created_at',
        'updated-at',
        'deleted_at',
        'contribution_type_id'
    ];

    public function contribution_type()
    {
        return $this->belongsTo(ContributionType::class);
    }
    public function affiliate() 
    {
        return $this->belongsTo(Affiliate::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function degree()
    {
        return $this->belongsTo(Degree::class);
    }
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
