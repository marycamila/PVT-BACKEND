<?php

namespace App\Models\Procedure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureModality extends Model
{
    use HasFactory;
    public $timestamps = false;
    public $guarded = ['id'];
    protected $fillable = [
        'procedure_type_id',
        'name', 
        'shortened',
        'is_valid'
    ];

    public function procedure_type()
    {
        return $this->belongsTo(ProcedureType::class);
    }

    public function procedure_requirements()
	{
		return $this->hasMany(ProcedureRequirement::class);
    }

}
