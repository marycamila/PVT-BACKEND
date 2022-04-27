<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\User;

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
}
