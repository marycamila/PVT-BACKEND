<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contribution\Reimbursement;
use App\Models\Contribution\Contribution;
use App\Models\Affiliate\Affiliate;

class Degree extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'hierarchy_id',
        'code',
        'name',
        'shortened',
        'correlative'
    ];

    public function hierarchy()
    {
        return $this->belongsTo(Hierarchy::class);
    }

    public function affiliates()
    {
        return $this->hasMany(Affiliate::class);
    } 
    
    public function reimbursements()
    {
    return $this->hasMany(Reimbursement::class);
    }
    public function contributions()
    {
    return $this->hasMany(Contribution::class);
    }
}
