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
        DB::statement("CREATE OR REPLACE FUNCTION public.contribution_affiliate_senasir_create_or_update(affiliate bigint, year_copy date, user_reg integer, payroll_senasir_id integer)
        RETURNS varchar
        as $$
        declare

           type_acction varchar;
           id_contribution_passive int;
        begin
            --*******************************************************************************
            --Funcion par crear o actualizar un nuevo registro en la tabla contribution_passive--
            --*******************************************************************************
            id_contribution_passive:= serch_affiliate_period_senasir(affiliate,year_copy);
             IF id_contribution_passive = 0 then
                   type_acction:= 'created';

               -- Creacion de un nuevo registro
                   INSERT INTO public.contribution_passives(user_id, affiliate_id, month_year, quotable, rent_pension, dignity_rent, interest, total, created_at,updated_at,affiliate_rent_class,is_valid, contributionable_type, contributionable_id)
                   SELECT user_reg as user_id, pvs.affiliate_id,year_copy as month_year, (pvs.payable_liquid-pvs.dignity_rent) as quotable, pvs.payable_liquid as rent_pension,pvs.dignity_rent as dignity_rent, 0 as interest, pvs.discount_contribution_muserpol as total,(select current_timestamp as created_at),(select current_timestamp as updated_at), CASE rent_class
                        when 'VIUDEDAD' then 'VIUDEDAD'
                        else 'VEJEZ'
                        end
                    as affiliate_rent_class,true as is_valid,'payroll_senasirs'::character varying as contributionable_type, payroll_senasir_id as contributionable_id from payroll_senasirs pvs
                    WHERE id=payroll_senasir_id;
             RETURN type_acction ;
            ELSE
                type_acction:= 'updated';
            -- Actualizar datos en la contribucion
               UPDATE contribution_passives
               SET user_id = user_reg,
               quotable = pvs.payable_liquid-pvs.dignity_rent,
               rent_pension = pvs.payable_liquid,
               dignity_rent= pvs.dignity_rent,
               total = pvs.discount_contribution_muserpol,
               updated_at = (select current_timestamp),
               affiliate_rent_class = CASE pvs.rent_class
                 when 'VIUDEDAD' then 'VIUDEDAD'
                 else 'VEJEZ'
                 end,
               is_valid = true,
               contributionable_type = 'payroll_senasirs'::character varying,
               contributionable_id = payroll_senasir_id
                  FROM (SELECT * FROM payroll_senasirs WHERE id = payroll_senasir_id) AS pvs
                  WHERE contribution_passives.id= id_contribution_passive;
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
