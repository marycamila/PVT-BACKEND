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
