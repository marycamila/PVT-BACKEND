<?php

namespace App\Models\Contribution;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Affiliate\Affiliate;

class PayrollValidatedSenasir extends Model
{
    use HasFactory;
    public $timestamps = true;
    public $guarded = ['id'];
    protected $fillable = [
        'affiliate_id',
        'a_o',
        'mes',
        'id_person_titular',
        'matricula_titular',
        'mat_dh',
        'departamento',
        'regional',
        'renta',
        'tipo_renta',
        'carnet_num_com',
        'paterno',
        'materno',
        'p_nombre',
        's_nombre',
        'ap_casada',
        'fecha_nacimiento',
        'clase_renta',
        'total_ganado',
        'total_descuentos',
        'liquido_pagable',
        'rentegro_r_basica',
        'renta_dignidad',
        'reintegro_renta_dignidad',
        'reintegro_aguinaldo',
        'reintegro_importe_adicional',
        'reintegro_inc_gestion',
        'descuento_aporte_muserpol',
        'descuento_covipol',
        'descuento_prestamo_muserpol',
        'carnet_num_com_tit',
        'pat_titular',
        'mat_titular',
        'p_nom_titular',
        's_nombre_titular',
        'ap_casada_titular',
        'fecha_nac_titular',
        'clase_renta_tit',
        'fec_fail_tit',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function affiliate()
    {
        return $this->belongsTo(Affiliate::class);
    }
}
