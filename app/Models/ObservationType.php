<?php

namespace App\Models;

use App\Models\Admin\Module;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ObservationType extends Model
{
    use HasFactory;

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}
