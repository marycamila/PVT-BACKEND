<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionImportPeriodPayrollContributionSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { DB::statement("CREATE OR REPLACE FUNCTION public.import_period_payroll_contribution_senasir (year_copy varchar, user_reg integer,year_period integer, month_period integer)
          RETURNS varchar
        as $$
        declare
            aid_contribution varchar;
            update_affiliate_spouse varchar;
            acction varchar;
                   -- Declaración EXPLICITA del cursor
                    cur_contribution CURSOR FOR select * from aid_contribution_affiliate_payroll_senasirs where a_o = year_period::INTEGER and mes = month_period::INTEGER;
                    registro aid_contribution_affiliate_payroll_senasirs%ROWTYPE;
                begin
                   --************************************************************************************
                   --Funcion importar planilla por periodo
                   --************************************************************************************
                   -- Procesa el cursor
                   FOR registro IN cur_contribution loop
                   --actualizacion de Contribuciones
                   aid_contribution:= contribution_affiliate_senasir_create_or_update(registro.affiliate_id,year_copy,user_reg,registro.id::INTEGER);
                   --actualizacion o creacion de esposa y actualizacion de algunos datos del afiliado
                   update_affiliate_spouse:= update_or_create_spouse_and_update_affiliate(registro.affiliate_id,user_reg,registro.id::INTEGER);

                   END LOOP;
                   acction:='Importación realizada con éxito';
                    RETURN acction;
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
        DB::statement("DROP FUNCTION import_period_payroll_contribution_senasir");
    }
}