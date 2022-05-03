<?php

namespace App\Models\Procedure;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Admin\Module;

class ProcedureType extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'module_id',
        'name',
        'created_at', 
        'updated_at',
        'second_name'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }    

    public function procedure_modalities()
    {
        return $this->hasMany(ProcedureModality::class);
    } 
}
