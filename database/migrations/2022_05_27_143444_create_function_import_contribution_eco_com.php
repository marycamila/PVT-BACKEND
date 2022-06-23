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

  DB::statement("CREATE OR REPLACE FUNCTION public.change_state_valid(id_user bigint, id_economic_complements bigint)
  returns character varying
  language plpgsql
   as $$
   declare
   count_reg numeric:= 0;
   message varchar;
   cur_contribution cursor for (
    select
	    ec.id,
	    cp.id as contribution_id
    from
    	economic_complements ec
    inner join discount_type_economic_complement dtec
    on
    	ec.id = dtec.economic_complement_id
    inner join eco_com_states ecs
    on
    	ec.eco_com_state_id = ecs.id
    inner join contribution_passives cp
    on
    	cp.contributionable_id = dtec.id
    	and cp.contributionable_type = 'discount_type_economic_complement'
    where
    	dtec.discount_type_id = 7
    	and ecs.eco_com_state_type_id =1
    	and is_valid is false
    	and ec.id = id_economic_complements);

   begin
    --******************************************************************************--
    --Función para validar la contribución is_valid true tabla contribution_passives--
    --******************************************************************************--

      for record_row in cur_contribution loop

   update
       public.contribution_passives
   set
       user_id = id_user,
       is_valid = true,
       updated_at = current_timestamp
   where
       contribution_passives.id = record_row.contribution_id;
   count_reg:= count_reg + 1;
   end loop;
   message:=concat('Los aporte validados son: ',count_reg);
   return message ;
   end;
   $$
   ;");
  DB::statement("CREATE OR REPLACE FUNCTION public.change_state_valid_false(id_user bigint,id_economic_complements bigint)
  returns character varying
  language plpgsql
  AS $$
    declare
    count_reg numeric := 0;

    message varchar;

    cur_contribution cursor for (
    select
        ec.id,
        cp.id as contribution_id
    from
        economic_complements ec
    inner join discount_type_economic_complement dtec
    on
        ec.id = dtec.economic_complement_id
    inner join contribution_passives cp
    on
        cp.contributionable_id = dtec.id
        and cp.contributionable_type = 'discount_type_economic_complement'
    where
        dtec.discount_type_id = 7
        and ec.eco_com_state_id in (16)
        and is_valid is true
        and ec.id = id_economic_complements);

    begin
        --******************************************************************************--
        --Función para validar la contribución is_valid false tabla contribution_passives--
        --******************************************************************************--
       for record_row in cur_contribution loop

    update
        public.contribution_passives
    set
        user_id = id_user,
        is_valid = false,
        updated_at = current_timestamp
    where
        contribution_passives.id = record_row.contribution_id;

    count_reg := count_reg + 1;
    end loop;

    message := concat('Los aporte que cambiaron de estado is_valid false son: ', count_reg);

    return message ;
    end;

    $$
    ;");

DB::statement("CREATE OR REPLACE FUNCTION public.import_contribution_eco_com(id_user bigint, eco_com_procedure bigint)
RETURNS character varying
LANGUAGE plpgsql
AS $$
       declare
       amount_semester numeric := 0;
       sum_amount numeric;
       amount_month numeric := 0;
       quotable_amount numeric := 0;
       amount_dignity_rent numeric := 0;
       contribution_id int ;
       rent_class varchar;
       array_length_months integer;
       _periods date[] := array[]::date[];
       data_base_name varchar = 'discount_type_economic_complement';
       message varchar;
          dates varchar;
        num_reg_economic_complement numeric := 0;
        num_reg_contribution_passives numeric := 0;
       month_row RECORD;
     
   --Declaración del cursor
       cur_discounts cursor for (
       select
           ec.id as eco_com_id,
           ec.affiliate_id,
           ec.total_rent,
           ec.dignity_pension,
           ec.eco_com_modality_id,
           ec.eco_com_procedure_id ,
           dtec.id as id_discont_type,
           dtec.amount,
           ecm.procedure_modality_id
           from discount_type_economic_complement dtec
               inner join economic_complements ec
                   on ec.id = dtec.economic_complement_id
               inner join eco_com_modalities ecm
                   on ecm.id = ec.eco_com_modality_id
           where  dtec.discount_type_id = 7		-- Amortización Auxilio Mortuorio
               and ec.eco_com_procedure_id = eco_com_procedure 	-- id semestre recibido como parámetro
               and ec.eco_com_state_id in (16)  	-- en proceso         
               and ec.deleted_at is null
                  and ec.wf_current_state_id =3);	 	-- área técnica
       begin	        
       --***********************************************************************************--
       --Registro de contribuciones de los descuentos calculados para el auxilio mortorio--
       --***********************************************************************************--
       -- Procesa el cursor
       for record_row in cur_discounts loop
       --Declaración y asignación de información a variables
           sum_amount := 0;
           amount_month := (select discount_amount_month(record_row.id_discont_type));  --obtiene el aporte mensual
                   if(record_row.dignity_pension is null) then amount_dignity_rent := 0;
                   else amount_dignity_rent := record_row.dignity_pension;
               end if;
           quotable_amount := record_row.total_rent - amount_dignity_rent;
           rent_class := (case
                               when record_row.procedure_modality_id = 29 then 'VEJEZ'
                               when record_row.procedure_modality_id = 30 then 'VIUDEDAD'
                          end);
           _periods :=(select get_periods_semester(record_row.eco_com_procedure_id)); -- obtiene los periodos de aporte de acuerdo al semestre
           array_length_months := array_length(_periods, 1);
      
               --Realiza recorrido de meses
               for i in 1.. array_length_months loop
                   contribution_id := (select cp.id from contribution_passives cp
                                       where cp.affiliate_id = record_row.affiliate_id and cp.month_year = _periods[i]::date);
                                   
                   if not exists(select cp.id from contribution_passives cp
                                 where cp.affiliate_id = record_row.affiliate_id
                                 and cp.month_year = _periods[i]::date
                                 and cp.contributionable_type ='discount_type_economic_complement'
                                 and cp.contributionable_id = record_row.id_discont_type) then		            
                   --Creación de Nuevos aportes--
                       insert
                           into
                               public.contribution_passives (user_id,
                               affiliate_id,
                               month_year,
                               quotable,
                               rent_pension,
                               dignity_rent,
                               interest,
                               total,
                               is_valid,
                               affiliate_rent_class,
                               contributionable_type,
                               contributionable_id,
                               created_at,
                               updated_at)
                           values(id_user,
                               record_row.affiliate_id,
                               _periods[i]::date,
                               quotable_amount::numeric,
                               record_row.total_rent::numeric,
                               amount_dignity_rent::numeric,
                               0::numeric,
                               amount_month::numeric,
                               false,
                               rent_class::character varying,
                               data_base_name::character varying,
                               record_row.id_discont_type,
                               current_timestamp,
                               current_timestamp);
       
                   elsif ((select count(cp.id) from contribution_passives cp
                                   where cp.affiliate_id = record_row.affiliate_id
                                   and cp.month_year = _periods[i]::date
                                   and cp.contributionable_type <> 'discount_type_economic_complement'
                                and cp.contributionable_id <> record_row.id_discont_type) >= 1
                                or (select count(cp.id) from contribution_passives cp
                                   where cp.affiliate_id = record_row.affiliate_id
                                   and cp.month_year = _periods[i]::date
                                   and cp.contributionable_type = 'discount_type_economic_complement'
                                and cp.contributionable_id = record_row.id_discont_type
                                and amount_month<>cp.total)>=1) then
                   
               --Actualización de aportes--
                          update public.contribution_passives
                              set
                               user_id = id_user,
                               quotable = quotable_amount::numeric,
                               rent_pension = record_row.total_rent::numeric,
                               dignity_rent = amount_dignity_rent::numeric,
                               total = amount_month::numeric,
                               is_valid = false,
                               affiliate_rent_class = rent_class::character varying,
                               contributionable_type = data_base_name::character varying,
                               contributionable_id = record_row.id_discont_type,
                               updated_at = current_timestamp
                           where contribution_passives.id = contribution_id;
                   end if;
                   --para generar el ultimo aporte
                   sum_amount = sum_amount + amount_month;
                   if(i = 5) then
                          amount_month := record_row.amount-sum_amount;
                      end if;
                      num_reg_economic_complement = num_reg_economic_complement + 1;  
               end loop;
            num_reg_contribution_passives = num_reg_contribution_passives + 1;              
       end loop;       
       message := 'Registro realizado exitosamente'||','||num_reg_economic_complement||','||num_reg_contribution_passives;
       return message;
       end;
       $$;
    ");

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