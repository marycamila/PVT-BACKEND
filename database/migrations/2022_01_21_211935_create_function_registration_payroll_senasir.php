<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionRegistrationPayrollSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.registration_payroll_senasir(conection_db_aux character varying,month_copy integer, year_copy integer)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
        declare
                  ----variables----
                 num_validated int := 0;
                 is_validated_update boolean := TRUE;
                 is_validated varchar ;
                 record_row RECORD;
                -------------------------------------------------------------------------
                ----FUNCION PARA REGISTRAR LOS DATOS VALIDADOS DE PLANILLA DE SENASIR----
                -------------------------------------------------------------------------
        BEGIN
        FOR record_row IN  select a.id as id_affiliate, a.id_person_senasir,payroll_copy_senasirs.*
           FROM affiliates a ,dblink(conection_db_aux,
             'select a_o, mes,
           id_person_titular as id_person, matricula_titular, mat_dh,departamento,regional,renta,tipo_renta,
           carnet,num_com, paterno, materno, p_nombre,s_nombre, ap_casada, fecha_nacimiento, clase_renta,total_ganado, total_descuentos,
           liquido_pagable, rentegro_r_basica, renta_dignidad, reintegro_renta_dignidad, reintegro_aguinaldo,reintegro_importe_adicional,
           reintegro_inc_gestion, descuento_aporte_muserpol, descuento_covipol, descuento_prestamo_musepol,carnet_tit,
           num_com_tit, pat_titular, mat_titular, p_nom_titular, s_nombre_titular, ap_casada_titular, fecha_nac_titular,
           clase_renta_tit, fec_fail_tit,is_validated
           FROM payroll_copy_senasirs ') AS payroll_copy_senasirs(a_o integer,
           mes integer,id_person_titular BIGINT , matricula_titular varchar,mat_dh varchar,
           departamento varchar,regional varchar, renta varchar,tipo_renta varchar,
           carnet varchar, num_com varchar,paterno varchar, materno varchar,p_nombre varchar,
               s_nombre varchar, ap_casada varchar,fecha_nacimiento date,clase_renta varchar,
               total_ganado NUMERIC(13,2),total_descuentos NUMERIC(13,2),
               liquido_pagable NUMERIC(13,2),rentegro_r_basica NUMERIC(13,2),
               renta_dignidad NUMERIC(13,2),reintegro_renta_dignidad NUMERIC(13,2),
               reintegro_aguinaldo NUMERIC(13,2),reintegro_importe_adicional NUMERIC(13,2),
               reintegro_inc_gestion NUMERIC(13,2), descuento_aporte_muserpol NUMERIC(13,2),
               descuento_covipol NUMERIC(13,2),descuento_prestamo_musepol NUMERIC(13,2),
               carnet_tit varchar, num_com_tit varchar,
               pat_titular varchar,mat_titular varchar,
               p_nom_titular varchar,s_nombre_titular varchar,
               ap_casada_titular varchar,fecha_nac_titular date,
               clase_renta_tit varchar,fec_fail_tit date,is_validated boolean)
           where a.id_person_senasir  = payroll_copy_senasirs.id_person_titular and payroll_copy_senasirs.clase_renta not like 'ORFANDAD%'
           and payroll_copy_senasirs.is_validated = false and payroll_copy_senasirs.a_o = year_copy and payroll_copy_senasirs.mes = month_copy
       LOOP
               INSERT INTO payroll_senasirs  
               VALUES (default,record_row.id_affiliate ,record_row.a_o, record_row.mes, 
               record_row.id_person_titular, record_row.matricula_titular, 
               record_row.mat_dh,
               record_row.departamento,record_row.regional,record_row.renta,record_row.tipo_renta,
               concat_identity_card_complement(record_row.carnet,record_row.num_com)::varchar, record_row.paterno, record_row.materno, record_row.p_nombre,
               record_row.s_nombre, record_row.ap_casada, record_row.fecha_nacimiento, record_row.clase_renta,record_row.total_ganado, record_row.total_descuentos,
               record_row.liquido_pagable, record_row.rentegro_r_basica, record_row.renta_dignidad, record_row.reintegro_renta_dignidad,record_row.reintegro_aguinaldo,
               record_row.reintegro_importe_adicional,
               record_row.reintegro_inc_gestion, 
               record_row.descuento_aporte_muserpol, 
               record_row.descuento_covipol, 
               record_row.descuento_prestamo_musepol,
               concat_identity_card_complement(record_row.carnet_tit,record_row.num_com_tit)::varchar, 
               record_row.pat_titular, record_row.mat_titular, record_row.p_nom_titular, record_row.s_nombre_titular, record_row.ap_casada_titular, 
               record_row.fecha_nac_titular,
               record_row.clase_renta_tit, record_row.fec_fail_tit,
               current_timestamp,current_timestamp);

             --ACTUALIZAR LA TABLA PAYROLL COPY SENASIR DE LA BASE DE DATOS AUX--
               is_validated := (select * from  dblink(conection_db_aux,
               'Update payroll_copy_senasirs set is_validated = '||is_validated_update||' where id_person_titular = '||record_row.id_person_titular||' and mes= '||record_row.mes||' and a_o = '||record_row.a_o||'' ) tt(
              is_validated varchar)) as is_validated;

              num_validated:= num_validated+1;

       END LOOP;
       RETURN num_validated;
       END $$;
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION registration_payroll_senasir");
    }
}
