<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionSearchAffilaiteHolder extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(" CREATE OR REPLACE FUNCTION public.search_affiliate_holder(value varchar)
        RETURNS numeric
        AS $$
        DECLARE
               id_affiliate integer;
        begin
	        select id into id_affiliate
			from affiliates a
			where a.registration like value;

	        IF id_affiliate is NULL THEN
	        return 0;
	        else
	        RETURN  id_affiliate;
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
        DB::statement("DROP FUNCTION function_search_affilaite_holder");
    }
}
