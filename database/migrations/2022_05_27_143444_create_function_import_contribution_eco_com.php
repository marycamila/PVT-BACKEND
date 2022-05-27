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
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.get_periods_semester(id_eco_com_procedure bigint)
        RETURNS date []
        LANGUAGE plpgsql
        AS $$
        declare
           _periods date[] := array[]::date[];
           rec record;
        begin
           select extract(year from ecp.year) as year_c, ecp.semester as semester from eco_com_procedures ecp where ecp.id = id_eco_com_procedure into rec;
               case
                   when (rec.semester = 'Primer') then
                           for month_c in 7..12 loop
                               _periods :=  array_append(_periods, (rec.year_c||'-'||month_c||'-'||01)::date);
                           end loop;
                   when (rec.semester = 'Segundo') then
                           for month_c in 1..6 loop				  
                                _periods :=  array_append(_periods, (rec.year_c + 1||'-'||month_c||'-'||01)::date);					   
                           end loop;
                   else
                       _periods := array[]::date[];
               end case;
       return _periods;
       END;
       $$
       ;");
     DB::statement("CREATE OR REPLACE FUNCTION public.discount_amount_month(discount_id bigint)
     returns numeric
      language plpgsql
     as $$
     declare
     amount_semester numeric := 0;

     amount_month numeric := 0;

     begin
     --*********************************************************************************************--
     --Función para obtener monto por mes aporte del complemento económico para el auxilio mortuorio--
     --*********************************************************************************************--
         select
         amount
     into
         amount_semester
     from
         discount_type_economic_complement
     where
         id = discount_id;

     amount_month := round(amount_semester / 6, 2);

     return amount_month;
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
        //Schema::dropIfExists('function_get_periods_semester');
    }
};