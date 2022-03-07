<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TmpCreateFunctionUpdateAffiliateIdsSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_update_affiliate_ids_senasir(db_name_intext text)
       RETURNS varchar
       as $$
       declare

       type_state varchar ;
       cant varchar ;
       quantity integer := 1;
       user_id integer := 1;
       pension_entity_id integer :=5;

      -- Declaración EXPLICITA del cursor
        cur_contribution CURSOR for (select * from dblink(db_name_intext,'SELECT id,state,clase_renta,matricula_titular,id_person_senasir ,concat_carnet_num_com_tit, paterno,materno,p_nombre,s_nombre,ap_casada,fecha_nacimiento FROM tmp_copy_data_senasirs tcds where tcds.state =''unrealized'' and clase_renta  in (''VEJEZ'',''INVALIDEZ'',''INC.PARCIAL PERMANEN'',''INC.PARCIAL PERMANEN'')'::text) as  tmp_copy_data_senasirs(id integer, state character varying(250), clase_renta character varying(250),matricula_titular character varying(250),id_person_senasir integer, concat_carnet_num_com_tit character varying(250),paterno character varying(250),materno character varying(250),p_nombre character varying(250),s_nombre character varying(250),ap_casada character varying(250),fecha_nacimiento date));

		begin
	   			--************************************************************************************
                --Funcion importar planilla por periodo
                --************************************************************************************
             -- Procesa el cursor
             FOR registro IN cur_contribution loop

                  IF quantity_regitration(registro.matricula_titular) = quantity then

                      UPDATE public.affiliates
                      SET id_person_senasir = registro.id_person_senasir,
                      updated_at = (select current_timestamp)
                      WHERE affiliates.registration = registro.matricula_titular;
                    type_state:='realizado';
	             else
		           IF quantity_identity_card(registro.concat_carnet_num_com_tit) = quantity then
			           	IF quantity_fullname(registro.p_nombre,registro.paterno,registro.materno) = quantity then
	        			     UPDATE public.affiliates
                      	     SET id_person_senasir = registro.id_person_senasir,
                      	     registration = registro.mat_titular,
                      		 updated_at = (select current_timestamp)
                      		 WHERE affiliates.identity_card = registro.concat_carnet_num_com_tit;
	         		    type_state:='actualizado';
	                    END IF;
	               else
                   IF quantity_identity_card(registro.concat_carnet_num_com_tit) = 0 then
	                  if registro.concat_carnet_num_com_tit is not null then
	                    type_state:='creado';
              			INSERT INTO public.affiliates (user_id,pension_entity_id, identity_card, registration, gender, last_name, mothers_last_name, first_name, second_name, surname_husband, birth_date, id_person_senasir,created_at,updated_at) 
               			VALUES(user_id,pension_entity_id, registro.concat_carnet_num_com_tit, registro.matricula_titular,'M', registro.paterno, registro.materno,registro.p_nombre, registro.s_nombre, registro.ap_casada, registro.fecha_nacimiento,registro.id_person_senasir,current_timestamp,current_timestamp);
                      END IF;
                    END IF;
	           		END IF;
				END IF;
        		if exists (select * from affiliates where id_person_senasir =registro.id_person_senasir) then
                    cant:=  (select dblink_exec(db_name_intext, 'UPDATE tmp_copy_data_senasirs SET state=''accomplished'' WHERE tmp_copy_data_senasirs.id= '||registro.id||''));                  
              	END IF;
              END LOOP;

 return type_state ;
end

$$ LANGUAGE 'plpgsql'
       ");
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
