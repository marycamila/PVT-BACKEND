<?php

namespace App\Models\Affiliate;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contribution\PayrollCommand;

class Breakdown extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'code',
        'name'
    ];
    public function payroll_commands()
    {
        return $this->hasMany(PayrollCommand::class);
    }
}
