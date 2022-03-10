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
        DB::statement("CREATE OR REPLACE FUNCTION public.update_or_create_spouse_and_update_affiliate(affiliate bigint, user_reg integer, payroll_validated_senasir_id integer)
        returns varchar
        as $$
        declare
        message varchar;
        id_pension_entity int;
        id_affiliate_state int;
        begin
            --*******************************************************************************************
            --Funcion par crear o actualizar datos de la esposa y actualizar algunos datos del afiliado--
            --*******************************************************************************************
            id_pension_entity:=  (SELECT id FROM pension_entities WHERE name ='SENASIR');
            id_affiliate_state:=  (SELECT id FROM affiliate_states WHERE name ='Fallecido');
            if exists(SELECT  * FROM payroll_validated_senasirs pvs WHERE pvs.id = payroll_validated_senasir_id and pvs.clase_renta='VIUDEDAD' and fec_fail_tit is not null and affiliate_id= affiliate) then
                if exists(SELECT * FROM spouses WHERE affiliate_id = affiliate) then
                 message:= 'actualiza esposa';
                      UPDATE public.spouses
                      SET user_id = user_reg,
                      registration = pvs.mat_dh,
                      updated_at = (select current_timestamp)
                      FROM (SELECT * FROM payroll_validated_senasirs WHERE id = payroll_validated_senasir_id) AS pvs
                      WHERE spouses.affiliate_id = 8480 and (spouses.registration in ('','0') or spouses.registration is null);      

                      UPDATE public.spouses
                      SET user_id = user_reg,
                      birth_date = pvs.fecha_nacimiento,
                      updated_at = (select current_timestamp)
                      FROM (SELECT * FROM payroll_validated_senasirs WHERE id = payroll_validated_senasir_id) AS pvs
                      WHERE spouses.affiliate_id = affiliate and (spouses.birth_date = '' or spouses.birth_date is null);

                else
                message:=  'crear esposa';
                      INSERT INTO public.spouses(user_id, affiliate_id,identity_card,registration, last_name, mothers_last_name , first_name , second_name, created_at,updated_at, birth_date)
                      SELECT user_reg as user_id, pvs.affiliate_id, pvs.carnet_num_com as identity_card, pvs.mat_dh as registration, pvs.paterno as last_name, pvs.materno as mothers_last_name,pvs.p_nombre as first_name, pvs.s_nombre as second_name,(select current_timestamp as created_at),(select current_timestamp as updated_at), pvs.fecha_nacimiento as birth_date
                      FROM payroll_validated_senasirs pvs
                      WHERE id=payroll_validated_senasir_id;
                end if;
               message:= 'Se actualizar afiliado y '||message;

               UPDATE public.affiliates
               SET user_id = user_reg,
               date_death = pvs.fec_fail_tit,
               updated_at = (select current_timestamp)
               FROM (SELECT * FROM payroll_validated_senasirs WHERE id = payroll_validated_senasir_id) AS pvs
               WHERE affiliates.id = affiliate and (affiliates.date_death = '' or affiliates.date_death is null);

               UPDATE public.affiliates
               SET user_id = user_reg,
               affiliate_state_id = id_affiliate_state,
               updated_at = (select current_timestamp)
               WHERE affiliates.id = affiliate and (affiliates.affiliate_state_id = '' or affiliates.affiliate_state_id is null);

            else
            message:=  'Se actualiza afiliado';
               UPDATE public.affiliates
               SET user_id = user_reg,
               birth_date = pvs.fecha_nacimiento, updated_at = (select current_timestamp)
               FROM (SELECT * FROM payroll_validated_senasirs WHERE id = payroll_validated_senasir_id) AS pvs
               WHERE affiliates.id = affiliate and (affiliates.birth_date = '' or affiliates.birth_date is null);

           end if;
               UPDATE affiliates
               SET user_id = user_reg,
               pension_entity_id = id_pension_entity,
               updated_at = (select current_timestamp)
               WHERE affiliates.id = affiliate and (affiliates.pension_entity_id = '' or affiliates.pension_entity_id is null);

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
