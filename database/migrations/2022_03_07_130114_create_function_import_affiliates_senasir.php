<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionImportAffiliatesSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_senasir_update_by_registration(conection_db_aux character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
        declare
           ----variables----
          num_update int := 0;
          state_update varchar := 'accomplished';
          tmp_update varchar;
          record_row RECORD;
        BEGIN
            FOR record_row IN select a.id as id_affiliate, tmp_copy.id_person_senasir, tmp_copy.matricula_titular , tmp_copy.id as id_tmp_copy
            --tmp_copy.p_nom_titular,tmp_copy.s_nombre_titular,tmp_copy.pat_titular,tmp_copy.mat_titular,tmp_copy.fec_fail_tit e ,tmp_copy.concat_carnet_num_com_tit ,
            --a.first_name,a.second_name ,a.last_name,a.mothers_last_name ,a.date_death, a.identity_card 
            --select count(*)
            from affiliates a, dblink(conection_db_aux,'select id,
            id_person_senasir,matricula_titular,mat_dh,carnet,num_com,concat_carnet_num_com,paterno,materno,p_nombre,s_nombre,
            ap_casada,fecha_nacimiento,lugar_nacimiento,clase_renta,pat_titular,mat_titular,p_nom_titular,s_nombre_titular,
            ap_casada_titular,carnet_tit,num_com_tit,concat_carnet_num_com_tit,fec_fail_tit,lugar_nacimiento_tit,state 
            from tmp_copy_data_senasirs') AS tmp_copy(id integer,
            id_person_senasir integer,matricula_titular varchar ,mat_dh varchar,carnet varchar,num_com varchar,concat_carnet_num_com varchar,
            paterno varchar,materno varchar,p_nombre varchar,s_nombre varchar,ap_casada varchar,fecha_nacimiento date,lugar_nacimiento varchar,
            clase_renta varchar,pat_titular varchar,mat_titular varchar,p_nom_titular varchar,s_nombre_titular varchar,
            ap_casada_titular varchar,carnet_tit varchar,num_com_tit varchar,concat_carnet_num_com_tit varchar,fec_fail_tit date,
            lugar_nacimiento_tit varchar,state varchar)
            where a.registration = tmp_copy.matricula_titular and tmp_copy.clase_renta  like 'VIUDEDAD' and a.id_person_senasir is null
            and tmp_copy.state like 'unrealized' --1409
            --and a.first_name = tmp_copy.p_nom_titular--1402
            --and a.last_name  = tmp_copy.pat_titular --1374
            --and a.mothers_last_name  = tmp_copy.mat_titular  --1362

            LOOP
                IF quantity_regitration(record_row.matricula_titular) = 1 THEN 
                     --ACTUALIZAR LA TABLA DE AFILIADOS CON IDS DE PERSONAS SENASIR----
                      UPDATE affiliates SET id_person_senasir  = record_row.id_person_senasir WHERE id = record_row.id_affiliate;
                     --ACTUALIZAR LA TABLA DE LA TABLA TEMPORAL DE PERSONAS SENASIR
                     tmp_update := (select * from  dblink(conection_db_aux,
                     'Update tmp_copy_data_senasirs set state = '''||state_update||''' where id = '||record_row.id_tmp_copy||'' ) tt(
                     state varchar)) as tmp_update;

                     num_update:= num_update+1;
                END IF;
             END LOOP;
             RETURN num_update;
           END $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_senasir_update_by_identity(conection_db_aux character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
        DECLARE
           ----variables----
          num_update int := 0;
          state_update varchar := 'accomplished';
          tmp_update varchar;
          record_row RECORD;
        BEGIN
            FOR record_row IN select a.id as id_affiliate, tmp_copy.id_person_senasir, tmp_copy.matricula_titular , tmp_copy.id as id_tmp_copy
            --select a.registration ,tmp_copy.matricula_titular,tmp_copy.p_nom_titular,tmp_copy.s_nombre_titular,tmp_copy.pat_titular,tmp_copy.mat_titular,tmp_copy.fec_fail_tit e ,tmp_copy.concat_carnet_num_com_tit ,
            --a.first_name,a.second_name ,a.last_name,a.mothers_last_name ,a.date_death, a.identity_card, tmp_copy.state
            --select count(*)
            from affiliates a, dblink(conection_db_aux,'select id,
            id_person_senasir,matricula_titular,mat_dh,carnet,num_com,concat_carnet_num_com,paterno,materno,p_nombre,s_nombre,
            ap_casada,fecha_nacimiento,lugar_nacimiento,clase_renta,pat_titular,mat_titular,p_nom_titular,s_nombre_titular,
            ap_casada_titular,carnet_tit,num_com_tit,concat_carnet_num_com_tit,fec_fail_tit,lugar_nacimiento_tit,state 
            from tmp_copy_data_senasirs') AS tmp_copy(id integer,
            id_person_senasir integer,matricula_titular varchar ,mat_dh varchar,carnet varchar,num_com varchar,concat_carnet_num_com varchar,
            paterno varchar,materno varchar,p_nombre varchar,s_nombre varchar,ap_casada varchar,fecha_nacimiento date,lugar_nacimiento varchar,
            clase_renta varchar,pat_titular varchar,mat_titular varchar,p_nom_titular varchar,s_nombre_titular varchar,
            ap_casada_titular varchar,carnet_tit varchar,num_com_tit varchar,concat_carnet_num_com_tit varchar,fec_fail_tit date,
            lugar_nacimiento_tit varchar,state varchar)
            where a.identity_card  = tmp_copy.concat_carnet_num_com_tit  and tmp_copy.clase_renta  like 'VIUDEDAD' and a.id_person_senasir is null
            and tmp_copy.state like 'unrealized' --299
            --and a.first_name = tmp_copy.p_nom_titular--261
            --and a.last_name  = tmp_copy.pat_titular --225
            --and a.mothers_last_name  = tmp_copy.mat_titular  --214

            LOOP

                --ACTUALIZAR LA TABLA DE AFILIADOS CON IDS DE PERSONAS SENASIR----
                UPDATE affiliates SET id_person_senasir  = record_row.id_person_senasir WHERE id = record_row.id_affiliate;
                --ACTUALIZAR LA TABLA DE LA TABLA TEMPORAL DE PERSONAS SENASIR
                tmp_update := (select * from  dblink(conection_db_aux,
                'Update tmp_copy_data_senasirs set state = '''||state_update||''' where id = '||record_row.id_tmp_copy||'' ) tt(
                state varchar)) as tmp_update;

                num_update:= num_update+1;

             END LOOP;
             RETURN num_update;
           END $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_senasir_update_by_full_name_fail(conection_db_aux character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
        DECLARE
           ----variables----
          num_update int := 0;
          state_update varchar := 'accomplished';
          tmp_update varchar;
          record_row RECORD;
        BEGIN
            FOR record_row IN select a.id as id_affiliate, tmp_copy.id_person_senasir, tmp_copy.matricula_titular , tmp_copy.id as id_tmp_copy
           -- select a.registration ,tmp_copy.matricula_titular,tmp_copy.p_nom_titular,tmp_copy.s_nombre_titular,tmp_copy.pat_titular,tmp_copy.mat_titular,tmp_copy.fec_fail_tit ,tmp_copy.concat_carnet_num_com_tit ,
            --a.first_name,a.second_name ,a.last_name,a.mothers_last_name ,a.date_death , a.identity_card, tmp_copy.state
            --select count(*)
            from affiliates a, dblink(conection_db_aux,'select id,
            id_person_senasir,matricula_titular,mat_dh,carnet,num_com,concat_carnet_num_com,paterno,materno,p_nombre,s_nombre,
            ap_casada,fecha_nacimiento,lugar_nacimiento,clase_renta,pat_titular,mat_titular,p_nom_titular,s_nombre_titular,
            ap_casada_titular,carnet_tit,num_com_tit,concat_carnet_num_com_tit,fec_fail_tit,lugar_nacimiento_tit,state 
            from tmp_copy_data_senasirs') AS tmp_copy(id integer,
            id_person_senasir integer,matricula_titular varchar ,mat_dh varchar,carnet varchar,num_com varchar,concat_carnet_num_com varchar,
            paterno varchar,materno varchar,p_nombre varchar,s_nombre varchar,ap_casada varchar,fecha_nacimiento date,lugar_nacimiento varchar,
            clase_renta varchar,pat_titular varchar,mat_titular varchar,p_nom_titular varchar,s_nombre_titular varchar,
            ap_casada_titular varchar,carnet_tit varchar,num_com_tit varchar,concat_carnet_num_com_tit varchar,fec_fail_tit date,
            lugar_nacimiento_tit varchar,state varchar)
            where --a.identity_card  = tmp_copy.concat_carnet_num_com_tit  and 
            tmp_copy.clase_renta  like 'VIUDEDAD' 
            and a.id_person_senasir is null
            and tmp_copy.state like 'unrealized' --
            and a.first_name = tmp_copy.p_nom_titular--
            --and a.second_name = tmp_copy.s_nombre_titular
            and a.last_name  = tmp_copy.pat_titular --1408
            and a.mothers_last_name  = tmp_copy.mat_titular  --68
            and a.date_death =tmp_copy.fec_fail_tit ---13
            LOOP
                --ACTUALIZAR LA TABLA DE AFILIADOS CON IDS DE PERSONAS SENASIR----
                UPDATE affiliates SET id_person_senasir  = record_row.id_person_senasir WHERE id = record_row.id_affiliate;
                --ACTUALIZAR LA TABLA DE LA TABLA TEMPORAL DE PERSONAS SENASIR
                tmp_update := (select * from  dblink(conection_db_aux,
                'Update tmp_copy_data_senasirs set state = '''||state_update||''' where id = '||record_row.id_tmp_copy||'' ) tt(
                state varchar)) as tmp_update;

                  num_update:= num_update+1;

             END LOOP;
             RETURN num_update;
           END $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_senasir_create_affiliates_senasir(conection_db_aux character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
        DECLARE
           ----variables----
          num_update int := 0;
          state_update varchar := 'accomplished';
          tmp_update varchar;
          record_row RECORD;
        BEGIN
            FOR record_row IN select *
            from dblink(conection_db_aux,'select id,
            id_person_senasir,matricula_titular,mat_dh,carnet,num_com,concat_carnet_num_com,paterno,materno,p_nombre,s_nombre,
            ap_casada,fecha_nacimiento,lugar_nacimiento,clase_renta,pat_titular,mat_titular,p_nom_titular,s_nombre_titular,
            ap_casada_titular,carnet_tit,num_com_tit,concat_carnet_num_com_tit,fec_fail_tit,lugar_nacimiento_tit,state 
            from tmp_copy_data_senasirs') AS tmp_copy(id integer,
            id_person_senasir integer,matricula_titular varchar ,mat_dh varchar,carnet varchar,num_com varchar,concat_carnet_num_com varchar,
            paterno varchar,materno varchar,p_nombre varchar,s_nombre varchar,ap_casada varchar,fecha_nacimiento date,lugar_nacimiento varchar,
            clase_renta varchar,pat_titular varchar,mat_titular varchar,p_nom_titular varchar,s_nombre_titular varchar,
            ap_casada_titular varchar,carnet_tit varchar,num_com_tit varchar,concat_carnet_num_com_tit varchar,fec_fail_tit date,
            lugar_nacimiento_tit varchar,state varchar)
            where tmp_copy.clase_renta  like 'VIUDEDAD' 
            and tmp_copy.state like 'unrealized'and tmp_copy.concat_carnet_num_com_tit is not null---118
            LOOP

                if quantity_identity_card(record_row.concat_carnet_num_com_tit) = 0
                and quantity_fullname(record_row.p_nom_titular,record_row.pat_titular,record_row.mat_titular) = 0
                and quantity_regitration(record_row.matricula_titular) = 0

                THEN

                    INSERT INTO affiliates (user_id,affiliate_state_id,pension_entity_id,id_person_senasir,
                    first_name, second_name, last_name, mothers_last_name,surname_husband ,
                    identity_card, registration,date_death,gender,created_at,updated_at)
                    VALUES (181,4,5,record_row.id_person_senasir ,
                    insert_text(record_row.p_nom_titular),
                    insert_text(record_row.s_nombre_titular),
                    insert_text(record_row.pat_titular),
                    insert_text(record_row.mat_titular),
                    insert_text(record_row.ap_casada_titular),
                    insert_text(record_row.concat_carnet_num_com_tit),
                    insert_text(record_row.matricula_titular),
                    record_row.fec_fail_tit,
                    insert_text('M'),
                    current_timestamp,
                    current_timestamp);

                    tmp_update := (select * from  dblink(conection_db_aux,
                    'Update tmp_copy_data_senasirs set state = '''||state_update||''' where id = '||record_row.id||'' ) tt(
                    state varchar)) as tmp_update;

                    num_update:= num_update+1;

                END IF;
             END LOOP;
             RETURN num_update;
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
        //Schema::dropIfExists('function_import_affiliates_senasir');
    }
}
