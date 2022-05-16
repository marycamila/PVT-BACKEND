<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contribution\PayrollCommand;

class Unit extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'breakdown_id',
        'district',
        'code',
        'name',
        'shortened'
    ];

    public function payroll_commands()
    {
        return $this->hasMany(PayrollCommand::class);
    }
}
