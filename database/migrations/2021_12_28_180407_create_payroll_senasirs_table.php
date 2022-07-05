<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollSenasirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payroll_senasirs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->unsigned()->comment('Id del afiliado titular'); // Id del afiliado titular
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->integer('year_p')->comment('Año del periodo de aporte');
            $table->integer('month_p')->comment('Mes del periodo de aporte');
            $table->unsignedBigInteger('id_person_senasir')->comment('id persona de titular senasir');
            $table->string('registration_a')->comment('Matricula afiliado titular');
            $table->string('registration_s')->nullable()->comment('Matricula derechohabiente');
            $table->string('department')->comment('Departamento');
            $table->string('regional')->comment('Regional');
            $table->string('rent')->comment('Renta');
            $table->string('rent_type')->comment('Tipo de renta');
            $table->string('identity_card')->nullable()->comment('Carnet numero');
            $table->string('last_name')->nullable()->comment('Apellido paterno');
            $table->string('mothers_last_name')->nullable()->comment('Apellido materno');
            $table->string('first_name')->comment('Primer nombre');
            $table->string('second_name')->nullable()->comment('Segundo nombre');
            $table->string('surname_husband')->nullable()->comment('Apellido de casada');
            $table->date('birth_date')->comment('Fecha de nacimiento');
            $table->string('rent_class')->comment('Clase de renta');
            $table->decimal('total_won', 13, 2)->comment('Total ganado');
            $table->decimal('total_discounts', 13, 2)->comment('Total descuentos');
            $table->decimal('payable_liquid', 13, 2)->comment('Liquido Pagable');
            $table->decimal('refund_r_basic', 13, 2)->comment('reintegro');
            $table->decimal('dignity_rent', 13, 2)->comment('Renta Dignidad');
            $table->decimal('refund_dignity_rent', 13, 2)->comment('Reintegro renta dignidad');
            $table->decimal('refund_bonus', 13, 2)->comment('Reintegro aguinaldo');
            $table->decimal('refund_additional_amount', 13, 2)->comment('Reintegro Importe Adicional');
            $table->decimal('refund_inc_management', 13, 2)->comment('Reintegro Inc Gestion');
            $table->decimal('discount_contribution_muserpol', 13, 2)->comment('Descuento aporte muserpol');
            $table->decimal('discount_covipol', 13, 2)->comment('Descuento covipol');
            $table->decimal('discount_loan_muserpol', 13, 2)->comment('Descuento prestamo muserpol');
            $table->string('identity_card_a')->nullable()->comment('Carnet numero afiliado titular');
            $table->string('last_name_a')->nullable()->comment('Apellido paterno afiliado titular');
            $table->string('mothers_last_name_a')->nullable()->comment('Apellido materno afiliado titular');
            $table->string('first_name_a')->nullable()->comment('Primer nombre afiliado titular');
            $table->string('second_name_a')->nullable()->comment('Segundo nombre afiliado titular');
            $table->string('surname_husband_a')->nullable()->comment('Ap casada afiliado  titular');
            $table->date('birth_date_a')->nullable()->comment('Fecha de nacimiento afiliado titular');
            $table->string('rent_class_a')->nullable()->comment('Clase de renta afiliado titular');
            $table->date('date_death_a')->nullable()->comment('Fecha de fallecimiento afiliado titular');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payroll_senasirs');
    }
}
