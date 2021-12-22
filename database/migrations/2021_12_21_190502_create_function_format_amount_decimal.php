<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionFormatAmountDecimal extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(" CREATE OR REPLACE FUNCTION public.format_amount_decimal(value varchar)
        RETURNS numeric
        AS $$
        DECLARE 
               mon varchar;
               deci varchar;
        begin   
               IF char_length(value)>0 then
                   mon = substring(value from 0 for 9);
                   deci = substring(value from 9 for 10);
                   RETURN concat(mon,'.',deci) ;
               else
                   RETURN  0;
               END IF;
        END;
       $$ LANGUAGE plpgsql;
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION format_amount_decimal");
    }
}
