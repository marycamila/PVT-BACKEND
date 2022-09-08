<?php

namespace App\Models\EconomicComplement;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EconomicComplement extends Model
{
    use HasFactory;

    public function eco_com_procedure()
    {
        return $this->belongsTo(EcoComProcedure::class);
    }
}
