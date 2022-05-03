<?php

namespace App\Models\Workflow;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\User;

class SequencesRecord extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'user_id',
        'wf_state_id',
        'message',
        'created_at', 
        'updated_at'
    ];
    
    public function user()
    {
      return $this->belongsTo(User::class); 
    }
    public function wf_state()
    {
      return $this->belongsTo(WfState::class); 
    }
}
