<?php

namespace App\Models\QuotaAidMortuary;

use App\Models\Admin\User;
use App\Models\Procedure\ProcedureType;
use App\Models\Workflow\WfState;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaAidCorrelative extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'wf_state_id',
        'quota_aid_mortuary_id',
        'code',
        'date',
        'procedure_type_id',
        'note'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function wf_state()
    {
        return $this->belongsTo(WfState::class, 'wf_state_id');
    }
    public function quota_aid_mortuary()
    {
        return $this->belongsTo(QuotaAidMortuary::class);
    }
    public function procedure_type()
    {
        return $this->belongsTo(ProcedureType::class);
    }
}
