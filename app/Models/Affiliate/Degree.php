<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
