<?php

namespace App\Models\QuotaAidMortuary;

use App\Models\Affiliate\Hierarchy;
use App\Models\Procedure\ProcedureModality;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuotaAidProcedure extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'hierarchy_id',
        'type_mortuary',
        'procedure_modality_id',
        'amount',
        'year',
        'months',
        'is_enabled'
    ];

    public function hierarchy()
    {
        return $this->belongsTo(Hierarchy::class);
    }
    public function procedure_modality()
    {
        return $this->belongsTo(ProcedureModality::class);
    }
    public function quota_aid_mortuaries()
	{
		return $this->hasMany(QuotaAidMortuary::class);
    }
}
