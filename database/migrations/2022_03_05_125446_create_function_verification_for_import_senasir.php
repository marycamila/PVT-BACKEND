<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionVerificationForImportSenasir extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.quantity_regitration(value character varying)
        RETURNS numeric
        LANGUAGE plpgsql
       AS $$
       DECLARE
         quantity integer;
       BEGIN
            select count(id) into quantity
           from affiliates a
           where a.registration like value;

           IF quantity is NULL then
               RETURN 0;
           ELSE
               RETURN  quantity;
           END IF;
       END;
       $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.quantity_identity_card(value character varying)
        RETURNS numeric
        LANGUAGE plpgsql
       AS $$
               DECLARE
                      quantity integer;
               begin
                   select count(id) into quantity
                   from affiliates a
                   where a.identity_card like value;

                   IF quantity is NULL THEN
                   return 0;
                   else
                   RETURN  quantity;
                   END IF;
               END;
              $$;
       ");
        DB::statement("CREATE OR REPLACE FUNCTION public.quantity_fullname(first_name_input character varying,last_name_input character varying,mothers_last_name_input character varying)
        RETURNS numeric
        LANGUAGE plpgsql
       AS $$
               DECLARE
                      quantity integer;
               begin
                   select count(id) into quantity
                   from affiliates a
                   where a.first_name like first_name_input and a.last_name like last_name_input and a.mothers_last_name like mothers_last_name_input;

                   IF quantity is NULL THEN
                   return 0;
                   else
                   RETURN  quantity;
                   END IF;
               END;
       $$;");
        DB::statement("CREATE OR REPLACE FUNCTION IIF(
            condition boolean, true_result TEXT, false_result TEXT
        ) RETURNS TEXT LANGUAGE plpgsql AS $$
        BEGIN
         IF condition THEN
            RETURN true_result;
         ELSE
            RETURN false_result;
         END IF;
        END
        $$;");

        DB::statement(" CREATE OR REPLACE FUNCTION insert_text(value character varying) RETURNS TEXT LANGUAGE plpgsql AS $$
        begin
            return IIF(length(trim(upper(value))) = 0, null, trim(upper(value)));
        END
        $$;");

        DB::statement("CREATE OR REPLACE FUNCTION public.quantity_procedure_affiliate(id_affiliate integer,value character varying)
        RETURNS integer
        LANGUAGE plpgsql
       AS $$
          DECLARE
                quantity integer;
                begin
                    CASE
                       WHEN (value = 'l' ) THEN
                           select count(*) into  quantity
                           from affiliates a , (select distinct la.affiliate_id
                           from loans l, loan_affiliates la
                           where l.id =la.loan_id and l.deleted_at is null ) as qam
                           where a.id_person_senasir is not null and a.id = qam.affiliate_id and a.id = id_affiliate;
                       WHEN (value = 'ec' ) THEN
                           select count(*) into  quantity
                           from affiliates a , (select distinct affiliate_id
                           from economic_complements where deleted_at is null ) as eco_com
                           where a.id_person_senasir is not null and a.id = eco_com.affiliate_id and a.id = id_affiliate;
                       WHEN (value = 'rf' ) THEN
                           select count(*) into  quantity
                           from affiliates a , (select distinct affiliate_id
                           from retirement_funds  where deleted_at is null ) as rf
                           where a.id_person_senasir is not null and a.id = rf.affiliate_id and a.id = id_affiliate;
                       WHEN (value = 'qam' ) THEN
                           select count(*) into  quantity
                           from affiliates a , (select distinct affiliate_id
                           from quota_aid_mortuaries where deleted_at is null) as qam
                           where a.id_person_senasir is not null and a.id = qam.affiliate_id and a.id = id_affiliate;
                       ELSE
                           quantity :=0;
                    END CASE;
           return quantity;
         END;
         $$
       ;");
       DB::statement("CREATE OR REPLACE FUNCTION public.identified_affiliate(order_entry integer,identity_card_entry character varying, registration_entry character varying, first_name_entry character varying,
       last_name_entry character varying,mothers_last_name_entry character varying,birth_date_entry date)
        RETURNS integer
        LANGUAGE plpgsql
       AS $$
                 DECLARE
                       affiliate_id integer;
                       count_id integer:= 0;
                       begin
                           CASE
                              WHEN (order_entry = 1 ) THEN
                                  select id into affiliate_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry and registration = registration_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry and birth_date = birth_date_entry;

                                  select count(id) into count_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry and registration = registration_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry and birth_date = birth_date_entry;
                              WHEN (order_entry = 2  ) THEN
                                  select id into affiliate_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry and birth_date = birth_date_entry;

                                  select count(id) into count_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry and birth_date = birth_date_entry;
                              WHEN (order_entry = 3  ) THEN
                                 select id into affiliate_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry and registration = registration_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry;

                                  select count(id) into count_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry and registration = registration_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry;
                              WHEN (order_entry = 4  ) THEN
                                  select id into affiliate_id from affiliates where id_person_senasir is null
                                  and registration = registration_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry and birth_date = birth_date_entry;

                                  select count(id) into count_id from affiliates where id_person_senasir is null
                                  and registration = registration_entry and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry and birth_date = birth_date_entry;
                              WHEN (order_entry = 5  ) THEN
                                  select id into affiliate_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry  and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry;

                                  select count(id) into count_id from affiliates where id_person_senasir is null
                                  and identity_card = identity_card_entry  and first_name = first_name_entry 
                                  and last_name = last_name_entry and mothers_last_name = mothers_last_name_entry;
                              ELSE
                                  affiliate_id :=0;
                           END CASE;

                   IF count_id = 1 is NULL THEN
                       affiliate_id := affiliate_id;
                   ELSIF  count_id = 0 then
                       affiliate_id :=  count_id;
                   ELSIF  count_id > 1 then
                       affiliate_id :=  -1;
                   END IF;
                  return affiliate_id;
                END;
                $$
       ;
       ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('function_verification_for_import_senasir');
    }
}
