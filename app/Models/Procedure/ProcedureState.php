<?php

namespace App\Models\Procedure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Contribution\ContributionProcess;

class ProcedureState extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'name',
        'description',
        'created_at', 
        'updated_at'
    ];

    public function contribution_processes()
	{
		return $this->hasMany(ContributionProcess::class);
    }
}
