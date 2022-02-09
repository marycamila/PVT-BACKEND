<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionUpdateOrCreateSpouseAndUpdateAffiliate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.update_or_create_spouse_and_update_affiliate(affiliate bigint, user_reg integer,aid_contribution_affiliate_payroll_senasir_id integer)
        returns varchar
        as $$
        declare
        message varchar;
        id_pension_entity int;
        id_affiliate_state int;
        begin
            --************************************************************************************
            --Funcion par crear o actualizar datos de la esposa y actualizar algunos datos del afiliado
            --************************************************************************************
            id_pension_entity:=  (SELECT id FROM pension_entities WHERE name ='SENASIR') as id;
            id_affiliate_state:=  (SELECT id FROM affiliate_states WHERE name ='Fallecido') as id;
            if exists(SELECT  * FROM aid_contribution_affiliate_payroll_senasirs acaps WHERE id = aid_contribution_affiliate_payroll_senasir_id and clase_renta='VIUDEDAD' and fec_fail_tit is not null and affiliate_id= affiliate) then 
                if exists(SELECT * FROM spouses WHERE affiliate_id = affiliate) then
                 message:= 'actualiza esposa';
                      UPDATE public.spouses
                      SET user_id = user_reg,
                      registration = acaps.mat_dh,
                      birth_date = acaps.fecha_nacimiento,
                      updated_at = (select current_timestamp)
                      FROM (SELECT * FROM aid_contribution_affiliate_payroll_senasirs WHERE id = aid_contribution_affiliate_payroll_senasir_id) AS acaps
                      WHERE spouses.affiliate_id = affiliate;

                else
                message:=  'crear esposa';
                      INSERT INTO public.spouses(user_id, affiliate_id,identity_card,registration, last_name, mothers_last_name , first_name , second_name, created_at,updated_at, birth_date)
                      SELECT user_reg as user_id, acasps.affiliate_id, acasps.carnet_num_com as identity_card,acasps.mat_dh as registration, acasps.paterno as last_name, acasps.materno as mothers_last_name,acasps.p_nombre as first_name, acasps.s_nombre as second_name,(select current_timestamp as created_at),(select current_timestamp as updated_at), acasps.fecha_nacimiento as birth_date
                      FROM aid_contribution_affiliate_payroll_senasirs acasps
                      WHERE id=aid_contribution_affiliate_payroll_senasir_id;
            end if;
               message:= 'Se actualizar afiliado y '||message;
               UPDATE affiliates
               SET user_id = user_reg,
               affiliate_state_id = id_affiliate_state,
               pension_entity_id = id_pension_entity,
               updated_at = (select current_timestamp)
               WHERE id = affiliate;
            else
            message:=  'Se actualiza afiliado';
                      UPDATE public.affiliates
                       SET user_id = user_reg,
                       pension_entity_id = id_pension_entity,
                       birth_date = acaps.fecha_nacimiento, updated_at = (select current_timestamp)
                       FROM (SELECT * FROM aid_contribution_affiliate_payroll_senasirs WHERE id = aid_contribution_affiliate_payroll_senasir_id) AS acaps
                       WHERE id = affiliate;
               end if;
            return  message;
        end
        $$ language 'plpgsql'
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION update_or_create_spouse_and_update_affiliate");
    }
}
