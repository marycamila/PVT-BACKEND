<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionSerchAffiliatePeriodSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(" CREATE OR REPLACE FUNCTION serch_affiliate_period_senasir(affiliate bigint, year_copy varchar)
        RETURNS integer
        as $$
        DECLARE
            id_contribution integer;
        begin
           --************************************************************************************
           --Funcion par buscar id de la contribucion de un afiliado de un periodo determinado
           --************************************************************************************ 
            SELECT ac.id INTO id_contribution  FROM aid_contributions ac WHERE ac.affiliate_id = affiliate AND ac.month_year = year_copy;
                IF id_contribution is NULL THEN
                    return 0;
                ELSE
                    RETURN  id_contribution;
                END IF;
        end;
        $$ LANGUAGE 'plpgsql';
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION serch_affiliate_period_senasir");
    }
}
