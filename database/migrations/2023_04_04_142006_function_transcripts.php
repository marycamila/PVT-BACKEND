<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   DB::statement("CREATE OR REPLACE FUNCTION public.identified_affiliate_transcript(order_entry integer, identity_card_entry character varying, first_name_entry character varying, second_name_entry character varying, last_name_entry character varying, mothers_last_name_entry character varying)
        RETURNS integer
        LANGUAGE plpgsql
        AS $$
                        DECLARE
                              affiliate_id integer;
                              begin
                                  CASE
                                     WHEN (order_entry = 1 ) THEN --Busqueda de afiliado por CI, nombre, paterno y materno iguales--
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry
                                         AND first_name ILIKE first_name_entry
                                         AND (COALESCE(last_name, '') ILIKE COALESCE(last_name_entry, ''))
                                         AND (COALESCE(mothers_last_name, '') ILIKE COALESCE(mothers_last_name_entry, ''));

                                     WHEN (order_entry = 2  ) THEN --Busqueda de afiliado por CI igual y nombre, paterno y materno similares--
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE  identity_card_entry
                                         AND word_similarity(first_name , first_name_entry) >= 0.5
                                         AND word_similarity(last_name, last_name_entry) >= 0.5
                                         AND word_similarity(mothers_last_name, mothers_last_name_entry) >= 0.5;

                                     WHEN (order_entry = 3  ) THEN --Busqueda de afiliado por CI sin complemento,nombre, paterno y materno iguales--
                                         select id into affiliate_id from affiliates where
                                         split_part(identity_card,'-',1) ILIKE identity_card_entry
                                         AND first_name ILIKE first_name_entry
                                         AND (COALESCE(last_name, '') ILIKE COALESCE(last_name_entry, ''))
                                         AND (COALESCE(mothers_last_name, '') ILIKE COALESCE(mothers_last_name_entry, ''));

                                     WHEN (order_entry = 4  ) THEN
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry;
                                     ELSE
                                      affiliate_id := 0;
                                  END CASE;

                            IF affiliate_id  is not NULL THEN
                               affiliate_id := affiliate_id;
                            ELSE
                               affiliate_id := 0;
                            END IF;
                         return affiliate_id;
                         END;
            $$;"
        );

        DB::statement("CREATE OR REPLACE FUNCTION public.search_affiliate_transcript(db_name_intext text,month integer,year integer)
        RETURNS character varying
        LANGUAGE plpgsql
        AS $$
          declare
          type_state varchar;
          affiliate_id_result integer;
          criterion_one integer:= 1;
          criterion_two integer:= 2;
          criterion_three integer:= 3;
          criterion_four integer:= 4;
          criterion_five integer:= 5;
          ------------------------------
          cant varchar ;
         ---------------------------------
       -- Declaración EXPLICITA del cursor
       cur_payroll CURSOR for (select * from dblink(db_name_intext,'SELECT id,obs,uni,mes,a_o,car,pat,mat,nom,nom2,niv,gra,sue,cat,gan,mus,est,carg,fro,ori,nac,ing,affiliate_id,state,criteria FROM  payroll_copy_transcripts where state = ''unrealized'' and mes='||month||' and a_o='||year||'')
       as  payroll_copy_transcripts(id integer,obs character varying(250),uni character varying(250),mes integer,a_o integer,car character varying(250),pat character varying(250),mat character varying(250),nom character varying(250),nom2 character varying(250),
       niv character varying(250),gra  character varying(250),sue decimal(13,2),cat decimal(13,2),gan decimal(13,2) ,mus decimal(13,2) ,est decimal(13,2),carg decimal(13,2),fro decimal(13,2),ori decimal(13,2),
       nac date,ing date,affiliate_id integer,state character varying(250) ,criteria character varying(250)));
       begin
            --************************************************************
            --*Funcion busqueda de afiliados transcripciones
            --************************************************************
            -- Procesa el cursor
       FOR record_row IN cur_payroll loop
           if identified_affiliate_transcript(criterion_one,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat) > 0 then
               affiliate_id_result := identified_affiliate_transcript(criterion_one,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat);
               type_state:='1-CI-PN-PA-SA';
               cant:= (select dblink_exec(db_name_intext, 'UPDATE payroll_copy_transcripts SET state=''accomplished'',criteria='''||type_state||''',affiliate_id='||affiliate_id_result||' WHERE payroll_copy_transcripts.id= '||record_row.id||''));
           elsif identified_affiliate_transcript(criterion_two,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat) > 0 THEN
               affiliate_id_result := identified_affiliate_transcript(criterion_two,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat);
               type_state:='2-CI-sPN-sPA-sSA';
               cant:= (select dblink_exec(db_name_intext, 'UPDATE payroll_copy_transcripts SET state=''accomplished'',criteria='''||type_state||''',affiliate_id='||affiliate_id_result||' WHERE payroll_copy_transcripts.id= '||record_row.id||''));
           elsif identified_affiliate_transcript(criterion_three,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat) > 0 THEN
               affiliate_id_result := identified_affiliate_transcript(criterion_three,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat);
               type_state:='3-partCI-PN-PA-SA';
               cant:= (select dblink_exec(db_name_intext, 'UPDATE payroll_copy_transcripts SET state=''accomplished'',criteria='''||type_state||''',affiliate_id='||affiliate_id_result||' WHERE payroll_copy_transcripts.id= '||record_row.id||''));
           elsif identified_affiliate_transcript(criterion_four,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat) > 0 THEN
               affiliate_id_result := identified_affiliate_transcript(criterion_four,record_row.car,record_row.nom,record_row.nom2,record_row.pat,record_row.mat);
               type_state:='4-CI';
               cant:= (select dblink_exec(db_name_intext, 'UPDATE payroll_copy_transcripts SET state=''accomplished'',criteria='''||type_state||''',affiliate_id='||affiliate_id_result||' WHERE payroll_copy_transcripts.id= '||record_row.id||''));
           else
               type_state:='5-CREAR';
               cant:= (select dblink_exec(db_name_intext, 'UPDATE payroll_copy_transcripts SET state=''accomplished'',criteria='''||type_state||''' WHERE payroll_copy_transcripts.id= '||record_row.id||''));
           END IF;
       END LOOP;
       return true;
       end;$$;");

       DB::statement("CREATE OR REPLACE FUNCTION public.create_affiliate_transcript(db_name_intext text ,user_id_reg integer, month_copy integer, year_copy integer)
       RETURNS character varying
       LANGUAGE plpgsql
      AS $$
                     declare
                     affiliate_state_id_reg_jub integer := 5;
                     id_affiliate integer := 0;
                     message_into varchar:= '';
                     type_reg integer := 10;
                     update_affiliate varchar;
                    
                    ------------------------------
                    --- Declaración EXPLICITA del cursor
                      cur_payroll_create_affiliate CURSOR for (select * from dblink(db_name_intext,'SELECT id,car,pat,mat,nom,nom2,
                      niv,gra,criteria,affiliate_id,mes,a_o  FROM payroll_copy_transcripts
                      where criteria=''5-CREAR''and affiliate_id is null and mes = '||month_copy||' and a_o = '||year_copy||'') 
                      as  payroll_copy_transcripts(id integer,car character varying(250),pat character varying(250),mat character varying(250),nom character varying(250),
                        nom2 character varying(250),niv character varying(250),gra character varying(250),criteria character varying(250), affiliate_id character varying(250),mes integer, a_o integer ));
                  begin
                      --************************************************************
                      --*Funcion para la creacion de affiliados
                      --************************************************************
                      -- Procesa el cursor
                    FOR record_row IN cur_payroll_create_affiliate loop
                        ---hierarchy_id_into := (select get_hierarchy_id(record_row.niv,record_row.gra));
                         INSERT INTO affiliates (user_id,affiliate_state_id,first_name, second_name, last_name, mothers_last_name,identity_card,gender,created_at,updated_at)
                         VALUES (user_id_reg, affiliate_state_id_reg_jub,record_row.nom,record_row.nom2,record_row.pat,record_row.mat,record_row.car,'M',current_timestamp,current_timestamp);
                        id_affiliate:= (select id  from affiliates a  where a.identity_card = record_row.car) ;
                        ---Realizar Actualización del affiliate _id en la tabla payroll_copy_transcripts
                        update_affiliate:=  (select dblink_exec(db_name_intext, 'UPDATE payroll_copy_transcripts SET affiliate_id='||id_affiliate||' WHERE payroll_copy_transcripts.id= '||record_row.id||''));  
                        ---Registro historial de creación del afiliado 
                         message_into := 'Creacion de Afiliado';
                         INSERT INTO affiliate_records(user_id, affiliate_id,type_id, message,created_at, updated_at)
                         VALUES (user_id_reg,id_affiliate,type_reg,message_into, current_timestamp, current_timestamp);
                    END LOOP;
                     
           
                      return true;
                   end;
        $$;");

        DB::statement(" CREATE OR REPLACE FUNCTION public.import_payroll_transcript(db_name_intext text ,user_id_reg integer, month_copy integer, year_copy integer)
        RETURNS character varying
        LANGUAGE plpgsql
        AS $$
                    declare
                     ------------------------------
                     --- Declaración EXPLICITA del cursor
                       cur_import_payroll_ CURSOR for (select * from dblink(db_name_intext,'SELECT id,uni,mes,a_o,car,pat,mat,nom,nom2,niv,gra,sue,cat,gan,mus,est,carg,fro,ori,affiliate_id FROM payroll_copy_transcripts
                       where  mes = '||month_copy||' and a_o = '||year_copy||'') 
                       as  payroll_copy_transcripts(id integer,uni character varying(250), mes integer, a_o integer, car character varying(250),pat character varying(250),mat character varying(250),nom character varying(250),nom2 character varying(250),
                        niv character varying(250),gra character varying(250),sue NUMERIC(13,2),cat NUMERIC(13,2),gan NUMERIC(13,2),mus NUMERIC(13,2),est NUMERIC(13,2), carg NUMERIC(13,2),fro NUMERIC(13,2), ori NUMERIC(13,2), affiliate_id character varying(250) ));
                    begin
                       --************************************************************
                       --*Funcion para importar planillas
                       --************************************************************
                       -- Procesa el cursor
                        FOR record_row IN cur_import_payroll_ loop
                          INSERT INTO payroll_transcripts(affiliate_id, unit_id, month_p, year_p, identity_card, last_name, mothers_last_name, first_name, second_name, hierarchy_id, degree_id, base_wage, seniority_bonus, gain, total, study_bonus, position_bonus, border_bonus, east_bonus, affiliate_type, created_at, updated_at) 
                          VALUES(record_row.affiliate_id::bigint, 1,record_row.mes, record_row.a_o,record_row.car,record_row.pat,record_row.mat,record_row.nom,record_row.nom2, 1, 1, record_row.sue,record_row.cat, record_row.gan,record_row.mus,record_row.est, record_row.carg, record_row.fro, record_row.ori,'REGULAR'::character varying,current_timestamp,current_timestamp);
                        END LOOP;
                       return true;
                    end;
        $$;");

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
};
