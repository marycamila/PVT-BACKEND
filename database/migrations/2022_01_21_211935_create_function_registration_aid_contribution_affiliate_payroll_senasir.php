<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionRegistrationAidContributionAffiliatePayrollSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(" CREATE OR REPLACE FUNCTION public.registration_aid_contribution_affiliate_payroll_senasir(month_copy integer, year_copy integer)
        RETURNS TABLE(a_o_retorno integer, mes_retorno integer, matricula_titular_retorno character varying, mat_dh_retorno character varying, departamento_retorno character varying, carnet_num_com_retorno character varying, paterno_retorno character varying, materno_retorno character varying, p_nombre_retorno character varying, s_nombre_retorno character varying, fecha_nacimiento_retorno date, clase_renta_retorno character varying, total_ganado_retorno numeric, liquido_pagable_retorno numeric, renta_dignidad_retorno numeric, descuento_muserpol_retorno numeric, pat_titular_retorno character varying, mat_titular_retorno character varying, p_nom_titular_retorno character varying, s_nombre_titular_retorno character varying, carnet_num_com_tit_retorno character varying, fec_fail_tit_retorno date)
        LANGUAGE plpgsql
       AS $$
                      DECLARE
                          -- Declaración EXPLICITA del cursor
                          cur_copy_senasir CURSOR FOR SELECT * FROM aid_contribution_copy_payroll_senasirs where aid_contribution_copy_payroll_senasirs.mes = month_copy 
                         and aid_contribution_copy_payroll_senasirs.a_o = year_copy and aid_contribution_copy_payroll_senasirs.clase_renta  not like 'ORFANDAD%'; 
                          registro aid_contribution_copy_payroll_senasirs%ROWTYPE;
                      BEGIN
                         -- Procesa el cursor
                         FOR registro IN cur_copy_senasir loop
                         if  search_affiliate_holder(registro.matricula_titular) >0 then
                         INSERT INTO aid_contribution_affiliate_payroll_senasirs 
                           VALUES (default,search_affiliate_holder(registro.matricula_titular),registro.a_o::INTEGER,registro.mes::INTEGER,registro.matricula_titular,
                           registro.mat_dh,registro.departamento,concat_identity_card_complement(registro.carnet,registro.num_com)::varchar,registro.paterno,registro.materno,
                           registro.p_nombre,registro.s_nombre,registro.fecha_nacimiento,registro.clase_renta,registro.total_ganado,registro.liquido_pagable,registro.renta_dignidad,registro.descuento_muserpol,
                           registro.pat_titular,registro.mat_titular,registro.p_nom_titular,registro.s_nombre_titular,
                           concat_identity_card_complement(registro.carnet_tit,registro.num_com_tit)::varchar,registro.fec_fail_tit,'registered',
                             current_timestamp,current_timestamp);
                        else
                         INSERT INTO aid_contribution_copy_payroll_senasirs_aux_no_exist 
                           VALUES (registro.a_o,
                          registro.mes ,
                          registro.matricula_titular ,
                          registro.mat_dh ,
                          registro.departamento ,
                          concat_identity_card_complement(registro.carnet,registro.num_com)::varchar,
                          registro.paterno ,
                          registro.materno ,
                          registro.p_nombre ,
                          registro.s_nombre,
                          registro.fecha_nacimiento,
                          registro.clase_renta,
                          registro.total_ganado,
                          registro.liquido_pagable,
                          registro.renta_dignidad,
                          registro.descuento_muserpol,
                          registro.pat_titular,
                          registro.mat_titular,
                          registro.p_nom_titular,
                          registro.s_nombre_titular,
                          concat_identity_card_complement(registro.carnet_tit,registro.num_com_tit)::varchar,
                          registro.fec_fail_tit
                        );

                        END IF;

                         END LOOP;
                         return query select a_o as a_o_retorno,
                          mes  as mes_retorno,
                          matricula_titular  as matricula_titular_retorno,
                          mat_dh  as mat_dh_retorno,
                          departamento  as departamento_retorno,
                          carnet_num_com as carnet_num_com_retorno,
                          paterno  as paterno_retorno,
                          materno  as materno_retorno,
                          p_nombre  as p_nombre_retorno,
                          s_nombre as s_nombre_retorno,
                            fecha_nacimiento as fecha_nacimiento_retorno,
                            clase_renta as clase_renta_retorno,
                          total_ganado as total_ganado_retorno,
                          liquido_pagable as liquido_pagable_retorno,
                          renta_dignidad as renta_dignidad_retorno,
                          descuento_muserpol as descuento_muserpol_retorno,
                          pat_titular as pat_titular_retorno,
                          mat_titular as mat_titular_retorno,
                          p_nom_titular as p_nom_titular_retorno,
                          s_nombre_titular as s_nombre_titular_retorno,
                          carnet_num_com_tit as carnet_num_com_tit_retorno,
                          fec_fail_tit  as fec_fail_tit_retorno
                         from aid_contribution_copy_payroll_senasirs_aux_no_exist
                        ---where mes = month_copy::INTEGER and a_o = year_copy::INTEGER;
                        return;
                      END $$
       ;
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION registration_aid_contribution_affiliate_payroll_senasir");
    }
}
