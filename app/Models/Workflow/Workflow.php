<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Module;
use App\Models\Contribution\ContributionProcess;

class Workflow extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'module_id',
        'name',
        'shortened'
    ];

    public function module()
    {
      return $this->belongsTo(Module::class); 
    } 
    public function contribution_processes()
	  {
	  	return $this->hasMany(ContributionProcess::class);
    }
    public function wf_sequences()
  	{
		  return $this->hasMany(WfSequence::class);
    }

}
