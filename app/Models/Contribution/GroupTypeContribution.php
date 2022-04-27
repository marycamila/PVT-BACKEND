<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupTypeContribution extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'name',
        'created_at',
        'updated_at'
    ];

    public function contribution_types()
    {
        return $this->hasMany(ContributionType::class);
    }
}
