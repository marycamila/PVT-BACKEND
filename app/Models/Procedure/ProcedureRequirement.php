<?php

namespace App\Models\Procedure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureRequirement extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'procedure_modality_id',
        'procedure_document_id',
        'number',
        'created_at', 
        'updated_at',
        'deleted_at'
    ];

    public function procedure_document()
    {
        return $this->belongsTo(ProcedureDocument::class);
    }
    public function procedure_modality()
    {
      return $this->belongsTo(ProcedureModality::class); 
    }
}
