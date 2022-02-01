<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionContributionAffiliateSenasirCreateOrUpdate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.contribution_affiliate_senasir_create_or_update(affiliate bigint, year_copy varchar, user_reg integer,aid_contribution_affiliate_payroll_senasir_id integer)
        RETURNS varchar
        as $$
        declare

           type_acction varchar;
           id_aid_contribution int;
           id_contribution_origin int;

        begin
            --************************************************************************************
            --Funcion par crear o actualizar un nuevo registro en la tabla aid_contribution
            --************************************************************************************
           id_aid_contribution:= serch_affiliate_period_senasir(affiliate,year_copy);
           id_contribution_origin:=  (select id from contribution_origins where name ='senasir') as id;
             IF id_aid_contribution = 0 then
                   type_acction:= 'created';

               -- Creacion de un nuevo registro

                   INSERT INTO public.aid_contributions (user_id, affiliate_id, month_year, type, quotable, rent, dignity_rent, interest, total, created_at, mortuary_aid,contribution_origin_id,affiliate_rent_class,valid)
                   SELECT user_reg as user_id, acasps.affiliate_id,year_copy as month_year ,'PLANILLA'::character varying as type, (acasps.liquido_pagable-acasps.renta_dignidad) as quotable, acasps.liquido_pagable as rent,acasps.renta_dignidad as dignity_rent, 0 as interest, acasps.descuento_muserpol as total,(select current_timestamp as created_at), 0 as mortuary_aid, id_contribution_origin as contribution_origin_id, CASE clase_renta
                        when 'VIUDEDAD' then 'VIUDEDAD'
                        else 'VEJEZ'
                        end
                    as affiliate_rent_class,true as valid from aid_contribution_affiliate_payroll_senasirs acasps 
                    WHERE id=aid_contribution_affiliate_payroll_senasir_id;

               -- Actualizar datos de la tabla aid_contribution_affiliate_payroll_senasirs con state created
                  UPDATE aid_contribution_affiliate_payroll_senasirs
                   SET state = type_acction WHERE id = aid_contribution_affiliate_payroll_senasir_id;

             RETURN type_acction ;
            ELSE
                type_acction:= 'updated';
            -- Creacion de copia para respaldo de la tabla aid_contribution antes de actualizar
               INSERT INTO public.temporary_registration_aid_contributions (contribution_aid_id,user_id, affiliate_id, month_year, type, quotable, rent, dignity_rent, interest, total,affiliate_contribution,mortuary_aid,valid,created_at,updated_at,deleted_at)
               SELECT id as contribution_aid_id ,user_id, affiliate_id, month_year, type, quotable, rent, dignity_rent, interest,total,affiliate_contribution,mortuary_aid,valid,created_at,updated_at,deleted_at FROM aid_contributions  WHERE id= id_aid_contribution;
            -- Actualizar datos en la contribucion
               UPDATE aid_contributions
               SET user_id = user_reg,
               type = 'PLANILLA'::character varying,
               quotable = acaps.liquido_pagable-acaps.renta_dignidad,
               rent = acaps.liquido_pagable,
               dignity_rent= acaps.renta_dignidad,
               total= acaps.descuento_muserpol,
               contribution_origin_id = id_contribution_origin,
               updated_at = (select current_timestamp),
               affiliate_rent_class = CASE acaps.clase_renta
                 when 'VIUDEDAD' then 'VIUDEDAD'
                 else 'VEJEZ'
                 end
                  FROM (SELECT * FROM aid_contribution_affiliate_payroll_senasirs WHERE id = aid_contribution_affiliate_payroll_senasir_id) AS acaps
                  WHERE aid_contributions.id= id_aid_contribution;

            -- Actualizar datos de la tabla aid_contribution_affiliate_payroll_senasirs con state updated
               UPDATE aid_contribution_affiliate_payroll_senasirs
               SET state = type_acction WHERE id = aid_contribution_affiliate_payroll_senasir_id;
              RETURN type_acction ;
            END IF;
        end;
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
        DB::statement("DROP FUNCTION contribution_affiliate_senasir_create_or_update");
    }
}
