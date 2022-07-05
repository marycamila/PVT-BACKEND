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
        DB::statement("CREATE OR REPLACE FUNCTION public.identified_affiliate_command(identity_card_into character varying,last_name_into  character varying,
        mothers_last_name_into  character varying,surname_husband_into  character varying, first_name_into  character varying, second_name_into  character varying,
         birth_date_into date,date_entry_into date )
         RETURNS numeric
         LANGUAGE plpgsql
        AS $$
                declare
                        -------------------------------------------------------------------------
                        ----FUNCION IDENTIFICAR AFILIADO DE COMANDO----
                        -------------------------------------------------------------------------
                       affiliate_id integer;
                       count_id int:=0;
                begin

                    if exists(SELECT id FROM affiliates WHERE identity_card = identity_card_into) then
                        SELECT id into affiliate_id FROM affiliates WHERE identity_card = identity_card_into;
                        SELECT count(id) into count_id FROM affiliates WHERE identity_card = identity_card_into;
                    else
                       select id into affiliate_id from affiliates where last_name = last_name_into and mothers_last_name = mothers_last_name_into and surname_husband = surname_husband_into and first_name = first_name_into   and second_name = second_name_into and birth_date = birth_date_into and date_entry  = date_entry_into ;

                       select count(id) into count_id from affiliates where last_name = last_name_into and mothers_last_name = mothers_last_name_into and surname_husband = surname_husband_into and first_name = first_name_into  and second_name = second_name_into and birth_date = birth_date_into and date_entry  = date_entry_into ;

                    end if;

                     IF count_id = 1 is NULL THEN
                        affiliate_id := affiliate_id;
                     ELSIF  count_id = 0 then
                        affiliate_id :=  0;
                     ELSIF  count_id > 1 then
                         affiliate_id :=  -1;
                     END IF;
                     RETURN affiliate_id;
                END;
            $$;");

        DB::statement("CREATE OR REPLACE FUNCTION public.replace_character(string character varying)
        RETURNS text
        LANGUAGE plpgsql
        AS $$
            BEGIN
                return  replace(string,'¥','Ñ');
            END;
        $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_affiliate_state_id(desg integer)
        RETURNS numeric
        LANGUAGE plpgsql
       AS $$
            BEGIN
                if (desg = 1 ) THEN
                   return 3;
                ELSIF (desg = 3 ) THEN
                   return 2;
                ELSE
                   return 1;
                end if;
          END;
       $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_type(desg integer)
        RETURNS text
        LANGUAGE plpgsql
       AS $$
            BEGIN
                if (desg = 5 ) THEN
                   return 'Batallón';
                ELSE
                   return 'Comando';
                end if;
          END;
       $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_breakdown_id(desg integer)
        RETURNS numeric
        LANGUAGE plpgsql
       AS $$
               DECLARE
                      breakdown_id integer;
               BEGIN
                   select id into breakdown_id
                   from breakdowns
                   where code = desg;

                   IF breakdown_id is NULL THEN
                   RETURN 10;
                   ELSE
                   RETURN  breakdown_id;
                   END IF;
               END;
           $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_unit_id(breakdown integer,uni character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
                DECLARE
                       unit_id integer;
                BEGIN
                         if (breakdown = 1 ) THEN
                               select id into unit_id from units where breakdown_id = breakdown and code = '20190' limit 1;
                         ELSIF (breakdown = 2 ) THEN
                               select id into unit_id from units where breakdown_id = breakdown and code = '20190' limit 1;
                         ELSIF (breakdown = 3 ) THEN
                               select id into unit_id from units where breakdown_id = breakdown and code = '20190' limit 1;
                         ELSE
                              if exists(SELECT id FROM units WHERE breakdown_id = breakdown and code = uni) then
                               select id into unit_id from units where breakdown_id = breakdown and code = uni limit 1;
                              else
                               select id into unit_id from units where code = uni limit 1;
                              end if;
                         end if;
                    RETURN  unit_id;
                END;
        $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_hierarchy_id(niv character varying,gra character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
                DECLARE
                       hierarchy_id integer;
                begin
                    if niv = '04' and gra = '15' then
                    niv:='03';
                    end if;
                   select id into hierarchy_id from hierarchies  where code = niv limit 1;
                    RETURN  hierarchy_id;
                END;
        $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_degree_id(hierarchy integer,gra character varying)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
                DECLARE
                       degree_id integer;
                begin

                   select id into degree_id from degrees where hierarchy_id = hierarchy and code = gra  limit 1;
                    RETURN  degree_id;
                END;
        $$;");
        DB::statement("CREATE OR REPLACE FUNCTION public.calc_category(cat_formato numeric ,sue_formato numeric)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
                begin
                    if cat_formato = 0 or sue_formato = 0 then
                        RETURN 0;
                    else
                        RETURN ROUND(cat_formato/sue_formato,2);
                    end if;
                END;
        $$;
        ");
        DB::statement("CREATE OR REPLACE FUNCTION public.get_category_id(cat_formato numeric ,sue_formato numeric)
        RETURNS numeric
        LANGUAGE plpgsql
        AS $$
                DECLARE
                       category_id integer;
                begin
                   select id into category_id from categories  where percentage = calc_category(cat_formato,sue_formato);
                    RETURN  category_id;
                END;
        $$;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('function_format_data_payroll_commands');
    }
};
