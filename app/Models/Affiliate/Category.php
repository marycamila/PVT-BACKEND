<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'from',
        'to',
        'name',
        'percentage'
    ];

    public function affiliates()
    {
        return $this->hasMany(Affiliate::class);
    }
}
