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
    {   DB::statement("CREATE OR REPLACE FUNCTION public.identified_affiliate_transcript(order_entry integer, identity_card_entry character varying, first_name_entry character varying, second_name_entry character varying, last_name_entry character varying, mothers_last_name_entry character varying)
        RETURNS integer
        LANGUAGE plpgsql
        AS $$
                        DECLARE
                              affiliate_id integer;
                              begin
                                  CASE
                                     WHEN (order_entry = 1 ) THEN --Busqueda de afiliado por CI, nombre, paterno y materno iguales--
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry
                                         AND first_name ILIKE first_name_entry
                                         AND (COALESCE(last_name, '') ILIKE COALESCE(last_name_entry, ''))
                                         AND (COALESCE(mothers_last_name, '') ILIKE COALESCE(mothers_last_name_entry, ''));

                                     WHEN (order_entry = 2  ) THEN --Busqueda de afiliado por CI igual y nombre, paterno y materno similares--
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE  identity_card_entry
                                         AND word_similarity(first_name , first_name_entry) >= 0.5
                                         AND word_similarity(last_name, last_name_entry) >= 0.5
                                         AND word_similarity(mothers_last_name, mothers_last_name_entry) >= 0.5;

                                     WHEN (order_entry = 3  ) THEN --Busqueda de afiliado por CI sin complemento,nombre, paterno y materno iguales--
                                         select id into affiliate_id from affiliates where
                                         split_part(identity_card,'-',1) ILIKE identity_card_entry
                                         AND first_name ILIKE first_name_entry
                                         AND (COALESCE(last_name, '') ILIKE COALESCE(last_name_entry, ''))
                                         AND (COALESCE(mothers_last_name, '') ILIKE COALESCE(mothers_last_name_entry, ''));

                                     WHEN (order_entry = 4  ) THEN
                                         select id into affiliate_id from affiliates where
                                         identity_card ILIKE identity_card_entry;
                                     ELSE
                                      affiliate_id := 0;
                                  END CASE;

                            IF affiliate_id  is not NULL THEN
                               affiliate_id := affiliate_id;
                            ELSE
                               affiliate_id := 0;
                            END IF;
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
