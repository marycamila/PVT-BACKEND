<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionConcatIdentityCardComplement extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.concat_identity_card_complement(value varchar,complement varchar)
        RETURNS varchar
        AS $$
        DECLARE
        begin
	        IF complement is null or trim(complement) ='' THEN
	        return value;
	        else
	        return concat(value,'-',complement);
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
        DB::statement("DROP FUNCTION concat_identity_card_complement");
    }
}
