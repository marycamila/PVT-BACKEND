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
    {   DB::statement("CREATE OR REPLACE FUNCTION public.identified_affiliate_transcribed(order_entry integer, identity_card_entry character varying, second_name_entry character varying, first_name_entry character varying, last_name_entry character varying, mothers_last_name_entry character varying)
        RETURNS integer
        LANGUAGE plpgsql
        AS $$
                        DECLARE
                              affiliate_id integer;
                              count_id integer:= 0;
                              begin
                                  CASE
                                     WHEN (order_entry = 1 ) THEN
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry and first_name ILIKE first_name_entry and second_name ILIKE second_name_entry 
                                         and last_name ILIKE last_name_entry and mothers_last_name ILIKE mothers_last_name_entry;

                                     WHEN (order_entry = 2 ) THEN
                                         select id into affiliate_id from affiliates where
                                         identity_card = identity_card_entry and first_name ILIKE first_name_entry 
                                         and last_name ILIKE last_name_entry;

                                     WHEN (order_entry = 3 ) THEN
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry
                                         and last_name ILIKE last_name_entry;

                                     WHEN (order_entry = 4 ) THEN
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry;
                                     ELSE
                                         affiliate_id := 0;
                                  END CASE;
                         return affiliate_id;
                         END;
        $$;"
    );

        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
