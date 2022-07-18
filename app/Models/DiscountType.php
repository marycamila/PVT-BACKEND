<?php

namespace App\Models;

use App\Models\Admin\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountType extends Model
{
    use HasFactory;

    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'module_id',
        'name',
        'shortened'
    ];

    public function module()
    {
        return $this->belongsTo(Module::class);
    } 
}
