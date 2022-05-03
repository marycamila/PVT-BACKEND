<?php

namespace App\Models\Procedure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcedureDocument extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'name',
        'created_at', 
        'updated_at',
        'expire_date'
    ];

    public function procedure_requirements()
    {
        return $this->hasMany(ProcedureRequirement::class);
    } 
}
