<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFunctionTmpContributionEcoCom extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE OR REPLACE FUNCTION public.tmp_contribution_eco_com()
        returns character varying
        language plpgsql
        as $$
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

        month_row RECORD;
        --Declaracio del cursor
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
        from
            discount_type_economic_complement dtec
        inner join economic_complements ec
        on
            ec.id = dtec.economic_complement_id
        inner join eco_com_modalities ecm
        on
            ecm.id = ec.eco_com_modality_id
        inner join eco_com_states ecs
        on
	        ec.eco_com_state_id = ecs.id
        where
            dtec.discount_type_id = 7
            and ecs.eco_com_state_type_id = 1
            and ec.deleted_at is null);

        begin
        --***********************************************************************************--
        --Registro de contibuciones de los descuentos ya realizados para el auxilio mortorio--
        --***********************************************************************************--
        -- Procesa el cursor
              for record_row in cur_discounts loop
        --Declaracio y asigno información a variables
        sum_amount := 0;

        amount_month := (
        select
            discount_amount_month(record_row.id_discont_type));

        if(record_row.dignity_pension is null) then
            amount_dignity_rent := 0;
        else
            amount_dignity_rent := record_row.dignity_pension ;
        end if;

        quotable_amount := record_row.total_rent - amount_dignity_rent;

        rent_class :=
        (case
            when record_row.procedure_modality_id = 29 then 'VEJEZ'
            when record_row.procedure_modality_id = 30 then 'VIUDEDAD'
        end);

        _periods :=(
        select
            get_periods_semester(record_row.eco_com_procedure_id));

        array_length_months := array_length(_periods, 1);
        --Realiza recorrido de meses
        for i in 1.. array_length_months loop

        contribution_id := (
        select
            cp.id
        from
            contribution_passives cp
        where
            cp.affiliate_id = record_row.affiliate_id
            and cp.month_year = _periods[i]::date);

        if not exists(
        select
            cp.id
        from
            contribution_passives cp
        where
            cp.affiliate_id = record_row.affiliate_id
            and cp.month_year = _periods[i]::date) then
        --Creacio de Nuevos aportes--
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
        values(1,
        record_row.affiliate_id,
        _periods[i]::date,
        quotable_amount::numeric,
        record_row.total_rent::numeric,
        amount_dignity_rent::numeric,
        0::numeric,
        amount_month::numeric,
        true,
        rent_class::character varying,
        data_base_name::character varying,
        record_row.id_discont_type,
        current_timestamp,
        current_timestamp);

        elsif exists(
        select
            cp.id
        from
            contribution_passives cp
        where
            cp.affiliate_id = record_row.affiliate_id
            and cp.month_year = _periods[i]::date) then
        --Actualización de aportes--
           update
            public.contribution_passives
        set
            user_id = 1,
            quotable = quotable_amount::numeric,
            rent_pension = record_row.total_rent::numeric,
            dignity_rent = amount_dignity_rent::numeric,
            total = amount_month::numeric,
            is_valid = true,
            affiliate_rent_class = rent_class::character varying,
            contributionable_type = data_base_name::character varying,
            contributionable_id = record_row.id_discont_type,
            updated_at = current_timestamp
        where
            contribution_passives.id = contribution_id;
        end if;
        --para generar el ultimo aporte
        sum_amount = sum_amount + amount_month;

        if(i = 5) then
              amount_month := record_row.amount-sum_amount;
        end if;
        end loop;
        end loop;

        message := 'Registro realizado exitosamente';

        return message;
        end;
        $$;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('tmp_contribution_eco_com');
    }
}
