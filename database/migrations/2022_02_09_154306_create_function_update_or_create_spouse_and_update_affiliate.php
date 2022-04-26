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
        DB::statement("CREATE OR REPLACE FUNCTION public.update_or_create_spouse_and_update_affiliate(affiliate bigint, user_reg integer, payroll_senasir_id integer)
        returns varchar
        as $$
        declare
        declare
        message varchar;
        id_affiliate_state int;
        begin
            --*******************************************************************************************
            --Funcion par crear o actualizar datos de la esposa y actualizar algunos datos del afiliado--
            --*******************************************************************************************

            id_affiliate_state:=  (SELECT id FROM affiliate_states WHERE name ='Fallecido');
            if exists(SELECT  * FROM payroll_senasirs ps WHERE ps.id = payroll_senasir_id and ps.rent_class='VIUDEDAD' and date_death_a is not null and affiliate_id= affiliate) then
                if exists(SELECT * FROM spouses WHERE affiliate_id = affiliate) then
                 message:= 'actualiza esposa';
                      UPDATE public.spouses
                      SET user_id = user_reg,
                      registration = ps.registration_s,
                      updated_at = (select current_timestamp)
                      FROM (SELECT * FROM payroll_senasirs WHERE id = payroll_senasir_id) AS ps
                      WHERE spouses.affiliate_id = affiliate and (spouses.registration in ('','0') or spouses.registration is null);

                      UPDATE public.spouses
                      SET user_id = user_reg,
                      birth_date = ps.birth_date,
                      updated_at = (select current_timestamp)
                      FROM (SELECT * FROM payroll_senasirs WHERE id = payroll_senasir_id) AS ps
                      WHERE spouses.affiliate_id = affiliate and spouses.birth_date is null;

                else
                message:=  'crear esposa';
                      INSERT INTO public.spouses(user_id, affiliate_id,identity_card,registration, last_name, mothers_last_name , first_name , second_name, created_at,updated_at, birth_date)
                      SELECT user_reg as user_id, ps.affiliate_id, ps.identity_card as identity_card, ps.registration_s as registration, ps.last_name as last_name, ps.mothers_last_name as mothers_last_name,ps.first_name as first_name, ps.second_name as second_name,(select current_timestamp as created_at),(select current_timestamp as updated_at), ps.birth_date as birth_date
                      FROM payroll_senasirs ps
                      WHERE id=payroll_senasir_id;
                end if;
               message:= 'Se actualizar afiliado y '||message;

               UPDATE public.affiliates
               SET user_id = user_reg,
               date_death = ps.date_death_a,
               updated_at = (select current_timestamp)
               FROM (SELECT * FROM payroll_senasirs WHERE id = payroll_senasir_id) AS ps
               WHERE affiliates.id = affiliate and affiliates.date_death is null;

               UPDATE public.affiliates
               SET user_id = user_reg,
               affiliate_state_id = id_affiliate_state,
               updated_at = (select current_timestamp)
               WHERE affiliates.id = affiliate and  affiliates.affiliate_state_id <> id_affiliate_state;

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
