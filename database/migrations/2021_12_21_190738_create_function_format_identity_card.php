<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionFormatIdentityCard extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.format_identity_card(value varchar)
        RETURNS varchar
       AS $$
       DECLARE
               format_identity_card varchar;
       begin
           IF char_length(value)>0 then
           format_identity_card = REPLACE(LTRIM(REPLACE(value, '0', ' ')),' ', '0');

               RETURN format_identity_card ;
           else
               RETURN  null;
           END IF;
       END;
       $$ LANGUAGE plpgsql;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP FUNCTION format_identity_card");
    }
}
