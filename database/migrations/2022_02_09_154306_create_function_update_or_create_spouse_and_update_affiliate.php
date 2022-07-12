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
        RETURNS character varying
        LANGUAGE plpgsql
       AS $$
               declare
               declare
               message varchar;
               message_record varchar:= '';
               rec_affiliate_state record;
               rec_payrroll_senasir record;
               rec_spouse record;
               rec_affiliate record;
               user_name varchar;
               begin
                   --*******************************************************************************************
                   --Funcion par crear o actualizar datos de la esposa y actualizar algunos datos del afiliado--
                   --*******************************************************************************************
                   select * from payroll_senasirs where id = payroll_senasir_id into rec_payrroll_senasir;
                   select * from affiliates where id = affiliate into rec_affiliate;
                   select username from users where id= user_reg into user_name;
                   select * from affiliate_states where name ='Fallecido' into rec_affiliate_state;

                   if exists(SELECT  * FROM payroll_senasirs ps WHERE ps.id = payroll_senasir_id and ps.rent_class='VIUDEDAD' and date_death_a is not null and affiliate_id = affiliate) then
                       if exists(SELECT * FROM spouses WHERE affiliate_id = affiliate) then
                         ---Se recupera datos de la esposa
                             select * FROM spouses WHERE affiliate_id = affiliate into rec_spouse;                
                             message:= 'actualiza esposa';
                            
                            if((insert_text(rec_payrroll_senasir.registration_s)is not null) and ((insert_text(rec_spouse.registration) is null) or (rec_spouse.registration = '0')) )then
                          ---Actualización de la matricula de la cónyuge
                             UPDATE public.spouses
                             SET user_id = user_reg,
                             registration = rec_payrroll_senasir.registration_s,
                             updated_at = (select current_timestamp)
                             WHERE spouses.affiliate_id = affiliate and (spouses.registration in ('','0') or spouses.registration is null);
                         ---Registro del historial de la actualización
                             message_record:= ('El usuario '||user_name||' actualizó matrícula de cónyuge '||rec_spouse.registration||' a '||insert_text(rec_payrroll_senasir.registration_s));                
                             INSERT INTO public.affiliate_records_pvt (user_id, affiliate_id, message, created_at, updated_at) VALUES(user_reg, affiliate,message_record,current_timestamp,current_timestamp);

                            end if;

                            if(rec_payrroll_senasir.birth_date is not null and rec_spouse.birth_date is null)then
                         ---Actualización de Fecha de nacimiento de la cónyuge
                             UPDATE public.spouses
                             SET user_id = user_reg,
                             birth_date = rec_payrroll_senasir.birth_date,
                             updated_at = (select current_timestamp)
                             WHERE spouses.affiliate_id = affiliate and spouses.birth_date is null;
                         ---Registro del historial de la actualización
                             message_record:= ('El usuario '||user_name||' actualizó la fecha de nacimiento de cónyuge a '||rec_payrroll_senasir.birth_date);
                             INSERT INTO public.affiliate_records_pvt (user_id, affiliate_id, message, created_at, updated_at) VALUES(user_reg, affiliate,message_record,current_timestamp,current_timestamp);
                            end if;

                       else
                       message:=  'crear esposa';
                           if(rec_payrroll_senasir.identity_card is not null)then
                             INSERT INTO public.spouses(user_id, affiliate_id,identity_card,registration, last_name, mothers_last_name , first_name , second_name,surname_husband, created_at,updated_at, birth_date)
                             values(user_reg,rec_payrroll_senasir.affiliate_id,insert_text(rec_payrroll_senasir.identity_card),insert_text(rec_payrroll_senasir.registration_s),insert_text(rec_payrroll_senasir.last_name),insert_text(rec_payrroll_senasir.mothers_last_name),insert_text(rec_payrroll_senasir.first_name),insert_text(rec_payrroll_senasir.second_name),insert_text(rec_payrroll_senasir.surname_husband),current_timestamp,current_timestamp,rec_payrroll_senasir.birth_date);

                            ---Registro del historial para la creación de cónyuge
                            message_record:= ('El usuario '||user_name||' registro a la cónyuge con  número de CI: '||rec_payrroll_senasir.identity_card||', matricula: '|| rec_payrroll_senasir.registration_s||', nombres y apellidos: '||concat_full_name(rec_payrroll_senasir.first_name, rec_payrroll_senasir.second_name, rec_payrroll_senasir.last_name, rec_payrroll_senasir.mothers_last_name, rec_payrroll_senasir.surname_husband)||', fecha de nacimiento: '||rec_payrroll_senasir.birth_date);
                            INSERT INTO public.affiliate_records_pvt (user_id, affiliate_id, message, created_at, updated_at) VALUES(user_reg, affiliate,message_record,current_timestamp,current_timestamp);
                           end if;
                       end if;
                      message:= 'Se actualizar afiliado y '||message;
                     if(rec_affiliate.date_death is null)then

                      UPDATE public.affiliates
                      SET user_id = user_reg,
                      date_death = rec_payrroll_senasir.date_death_a,
                      updated_at = (select current_timestamp)
                      WHERE affiliates.id = affiliate and affiliates.date_death is null;

                     ---Registro del historial de la actualización
                       message_record:= ('El usuario '||user_name||' registro la fecha de fallecimiento del afiliado '||rec_payrroll_senasir.date_death_a);
                        INSERT INTO public.affiliate_records_pvt (user_id, affiliate_id, message, created_at, updated_at) VALUES(user_reg, affiliate,message_record,current_timestamp,current_timestamp);
                     end if;
                    if(rec_affiliate.affiliate_state_id <> rec_affiliate_state.id)then
                      UPDATE public.affiliates
                      SET user_id = user_reg,
                      affiliate_state_id = rec_affiliate_state.id,
                      updated_at = (select current_timestamp)
                      WHERE affiliates.id = affiliate and  affiliates.affiliate_state_id <> rec_affiliate_state.id;

                      ---Registro del historial de la actualización
                       message_record:= ('El usuario '||user_name||' cambio de estado de '||(select name from affiliate_states where id=rec_affiliate.affiliate_state_id)||' a '||rec_affiliate_state.name);
                        INSERT INTO public.affiliate_records_pvt (user_id, affiliate_id, message, created_at, updated_at) VALUES(user_reg, affiliate,message_record,current_timestamp,current_timestamp);
                    end if;

                  end if;
                   return  message;
               end
               $$
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
       DB::statement("DROP FUNCTION update_or_create_spouse_and_update_affiliate");
    }
}
