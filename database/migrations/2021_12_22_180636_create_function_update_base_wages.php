<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionUpdateBaseWages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION update_base_wages(month_base_wages integer,year_base_wage integer, user_id integer,date_base_wages date) RETURNS SETOF base_wages  AS
        $$
        DECLARE
            -- Declaración EXPLICITA del cursor
            cur_hierarchie_degree CURSOR FOR select d.id,h.code as hierarchie_code, d.code as degrees_code, h.name ,d.name from degrees d ,hierarchies h where h.id = d.hierarchy_id order by d.id; 
            registro base_wages%ROWTYPE;

           cur_sue_update CURSOR FOR select distinct(cfpc.niv),cfpc.gra,sue from contribution_format_payroll_commands cfpc where sue <> 0 and mes = month_base_wages::INTEGER and a_o = year_base_wage::INTEGER order by cfpc.niv;
           data_update contribution_format_payroll_commands%ROWTYPE;

          insert_data BOOLEAN := FALSE;
          exist_data_cur_sue_update BOOLEAN := FALSE;

        BEGIN
           -- Procesa el cursor
             FOR registro IN cur_hierarchie_degree loop
                    insert_data = FALSE;
                    FOR data_update IN cur_sue_update loop
                        exist_data_cur_sue_update = true;
                           IF  registro.hierarchie_code = data_update.niv and registro.degrees_code = data_update.gra THEN
                            INSERT INTO base_wages 
                            VALUES (default,user_id,registro.id,date_base_wages,data_update.sue,current_timestamp,current_timestamp);
                            insert_data = TRUE;
                            EXIT;
                        END IF;
                        END LOOP;
                       if insert_data = false and exist_data_cur_sue_update = true then
                       INSERT INTO base_wages 
                            VALUES (default,user_id,registro.id,date_base_wages,0,current_timestamp,current_timestamp);
                            insert_data = true;
                       END IF;
               END LOOP;
           return query select * from base_wages bw  where month_year = date_base_wages;
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
        DB::statement("DROP FUNCTION update_base_wages");
    }
}
