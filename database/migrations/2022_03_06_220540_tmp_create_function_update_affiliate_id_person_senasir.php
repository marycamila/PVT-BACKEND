<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TmpCreateFunctionUpdateAffiliateIdPersonSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_update_affiliate_id_person_senasir(db_name_intext text)
        RETURNS character varying
        LANGUAGE plpgsql
       AS $$
                      declare
              
                      type_state varchar;
                      cant varchar ;
                      quantity integer := 1;
                      user_id integer := 1;
                      pension_entity_id integer :=5;
                    affiliate_state_id  integer :=4;
              
                   count_update_by_registration integer := 0;
                   count_update_by_registration_fullname integer := 0;
                   count_update_by_identity integer := 0;
                   count_update_by_identity_fullname integer := 0;
                   count_created_affiliate integer := 0;
              
                     -- Declaración EXPLICITA del cursor
                       cur_payroll CURSOR for (select * from dblink(db_name_intext,'SELECT id,id_person_senasir,matricula_tit,carnet_tit,num_com_tit,concat_carnet_num_com_tit,
                       p_nombre_tit,s_nombre_tit,paterno_tit,materno_tit,ap_casada_tit,fecha_nacimiento_tit,
                   genero_tit,fec_fail_tit,matricula_dh,carnet_dh,num_com_dh,concat_carnet_num_com_dh,
                   p_nombre_dh,s_nombre_dh,paterno_dh,materno_dh,ap_casada_dh,fecha_nacimiento_dh,
                   genero_dh,fec_fail_dh,clase_renta_dh,state,observacion FROM copy_person_senasirs 
                       where state = ''unrealized''') 
                      as  copy_person_senasirs(id integer,id_person_senasir integer ,matricula_tit character varying(250),carnet_tit character varying(250),num_com_tit character varying(250),concat_carnet_num_com_tit character varying(250),
                   p_nombre_tit character varying(250),s_nombre_tit character varying(250),paterno_tit character varying(250),materno_tit character varying(250),ap_casada_tit character varying(250),fecha_nacimiento_tit date,
                   genero_tit character varying(250),fec_fail_tit date,matricula_dh character varying(250),carnet_dh character varying(250),num_com_dh character varying(250),concat_carnet_num_com_dh character varying(250),
                   p_nombre_dh character varying(250),s_nombre_dh character varying(250),paterno_dh character varying(250),materno_dh character varying(250),ap_casada_dh character varying(250),fecha_nacimiento_dh date,
                   genero_dh character varying(250),fec_fail_dh date,clase_renta_dh character varying(250),state character varying(250),observacion character varying));
              
                   begin
                               --************************************************************
                               --*Funcion actualizacion de ids y creacion de afiliados*
                               --************************************************************
                               -- Procesa el cursor
                            FOR record_row IN cur_payroll loop
              
                                 IF quantity_regitration(record_row.matricula_tit) = quantity then
                                     UPDATE public.affiliates
                                     SET id_person_senasir = record_row.id_person_senasir,
                                     updated_at = (select current_timestamp)
                                     WHERE affiliates.registration = record_row.matricula_tit;
                                     
                                   --  IF quantity_fullname(record_row.p_nombre_tit,record_row.paterno_tit,record_row.materno_tit) = quantity then
                                    --  type_state:='ACTUALIZADO_POR_MATRICULA_NOMBRE_PM';
                                   --   count_update_by_registration_fullname:= count_update_by_registration_fullname + 1;
                                -- else
                                      type_state:='ACTUALIZADO_POR_MATRICULA';
                                      count_update_by_registration:= count_update_by_registration + 1;
                                 --END IF;
                              else
                              IF quantity_identity_card(record_row.concat_carnet_num_com_tit) = quantity and record_row.concat_carnet_num_com_tit != '0' then
                              
                                   UPDATE public.affiliates
                                      SET id_person_senasir = record_row.id_person_senasir,
                                      --registration = record_row.matricula_tit,
                                      updated_at = (select current_timestamp)
                                      WHERE affiliates.identity_card = record_row.concat_carnet_num_com_tit;
                              
                                   -- IF quantity_fullname(record_row.p_nombre_tit,record_row.paterno_tit,record_row.materno_tit) = quantity then
                                   -- type_state:='ACTUALIZADO_POR_CARNET_NOMBRE_PM';
                                   -- count_update_by_identity_fullname:= count_update_by_identity_fullname + 1;
                                  -- else
                                     type_state:='ACTUALIZADO_POR_CARNET';
                                    count_update_by_identity:= count_update_by_identity + 1;
                                   -- END IF;
                                else
                                  IF quantity_identity_card(record_row.concat_carnet_num_com_tit) = 0 then
                                   if record_row.concat_carnet_num_com_tit is not null then
                                     type_state:='AFILIADO_CREADO';
                                     count_created_affiliate:= count_created_affiliate + 1;
              
                           INSERT INTO affiliates (user_id,affiliate_state_id,pension_entity_id,id_person_senasir,
                           first_name, second_name, last_name, mothers_last_name,surname_husband ,
                           identity_card, registration,date_death,gender,created_at,updated_at)
                           VALUES (user_id,affiliate_state_id,pension_entity_id,record_row.id_person_senasir ,
                           insert_text(record_row.p_nombre_tit),
                           insert_text(record_row.s_nombre_tit),
                           insert_text(record_row.paterno_tit),
                           insert_text(record_row.materno_tit),
                           insert_text(record_row.ap_casada_tit),
                           insert_text(record_row.concat_carnet_num_com_tit),
                           insert_text(record_row.matricula_tit),
                           record_row.fec_fail_tit,
                           record_row.genero_tit,
                           current_timestamp,
                           current_timestamp);
                                     END IF;
                                   END IF;
                                END IF;
                       END IF;
                           if exists (select * from affiliates where id_person_senasir =record_row.id_person_senasir) then
                                   cant:=  (select dblink_exec(db_name_intext, 'UPDATE copy_person_senasirs SET state=''accomplished'',observacion='''||type_state||''' WHERE copy_person_senasirs.id= '||record_row.id||''));                  
                               END IF;
                             END LOOP;
                --return count_update_by_registration||','||count_update_by_registration_fullname||','||count_update_by_identity||','||count_update_by_identity_fullname||','||count_created_affiliate;
               return count_update_by_registration||','||count_update_by_identity||','||count_created_affiliate;
               end
               
               $$
       ;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
