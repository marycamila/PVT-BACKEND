<?php

namespace App\Models\QuotaAidMortuary;

use App\Models\Procedure\ProcedureRequirement;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaAidSubmittedDocument extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'quota_aid_mortuary_id',
        'procedure_requirement_id',
        'reception_date',
        'comment',
        'is_valid'
    ];

    public function quota_aid_mortuary()
    {
        return $this->belongsTo(QuotaAidMortuary::class);
    }
    public function procedure_requirement()
    {
        return $this->belongsTo(ProcedureRequirement::class);
    }    
}
