<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\User;
use App\Models\Procedure\ProcedureState;
use App\Models\Workflow\Workflow;
use App\Models\Workflow\WfState;

class ContributionProcess extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'wf_state_current_id',
        'workflow_id',
        'procedure_state_id',
        'direct_contribution_id',
        'date',
        'code',
        'inbox_state',
        'created_at',
        'updated_at',
        'total'
    ];
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function procedure_state()
    {
      return $this->belongsTo(ProcedureState::class); 
    }
    public function workflow()
    {
      return $this->belongsTo(Workflow::class); 
    }
    public function wf_state()
    {
        return $this->belongsTo(WfState::class,'wf_state_current_id','id');
    }
}
