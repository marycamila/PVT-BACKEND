<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionFormatContributionFormatPayrollCommand extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION format_contribution_format_payroll_command(month_copy varchar,year_copy varchar) RETURNS SETOF contribution_format_payroll_commands  AS
        $$
        DECLARE
            -- Declaración EXPLICITA del cursor
            cur_clientes CURSOR FOR SELECT * FROM contribution_copy_payroll_commands where contribution_copy_payroll_commands.mes like month_copy and contribution_copy_payroll_commands.a_o like year_copy; 
            registro contribution_copy_payroll_commands%ROWTYPE;
        BEGIN
           -- Procesa el cursor
           FOR registro IN cur_clientes LOOP
               INSERT INTO contribution_format_payroll_commands 
                VALUES (default,registro.uni::INTEGER,registro.desg::INTEGER,registro.mes::INTEGER,registro.a_o::INTEGER,
                format_identity_card(registro.car),registro.pat,registro.mat,registro.apes ,registro.nom ,
                registro.nom2 ,registro.eciv,registro.niv ,registro.gra ,registro.sex,
                format_amount_decimal(registro.sue),format_amount_decimal(registro.cat),format_amount_decimal(registro.est),format_amount_decimal(registro.carg),format_amount_decimal(registro.fro),
                format_amount_decimal(registro.ori),format_amount_decimal(registro.bseg),format_amount_decimal(registro.gan),format_amount_decimal(registro.mus),format_amount_decimal(registro.lpag),
                TO_DATE(registro.nac,'DDMMYYYY'),
                TO_DATE(registro.ing,'DDMMYYYY'),current_timestamp,current_timestamp);
           END LOOP;
           return query select * from contribution_format_payroll_commands where mes = month_copy::INTEGER and a_o = year_copy::INTEGER;
          return;
        END $$ LANGUAGE 'plpgsql';");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION format_contribution_format_payroll_command");
    }
}
