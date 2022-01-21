<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAidContributionCopyPayrollSenasirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aid_contribution_copy_payroll_senasirs', function (Blueprint $table) {
            $table->id();
            $table->integer('a_o')->comment('año');
            $table->integer('mes')->comment('Mes');
            $table->string('matricula_titular')->comment('Matricula titular');
            $table->string('mat_dh')->nullable()->comment('Matricula derechohabiente');
            $table->string('departamento')->comment('Departamento');
            $table->string('carnet')->nullable()->comment('Carnet numero');
            $table->string('num_com')->nullable()->comment('complemento del numero de carnet');
            $table->string('paterno')->nullable()->comment('Apellido paterno');
            $table->string('materno')->nullable()->comment('Apellido materno');
            $table->string('p_nombre')->comment('Primer nombre');
            $table->string('s_nombre')->nullable()->comment('Segundo nombre');
            $table->date('fecha_nacimiento')->comment('Fecha de nacimiento');
            $table->string('clase_renta')->comment('Clase de renta');
            $table->decimal('total_ganado', 13, 2)->comment('Total ganado');
            $table->decimal('liquido_pagable', 13, 2)->comment('Liquido Pagable');
            $table->decimal('renta_dignidad', 13, 2)->comment('Renta Dignidad');
            $table->decimal('descuento_muserpol', 13, 2)->comment('Descuento muserpol');
            $table->string('pat_titular')->nullable()->comment('Apellido paterno titular');
            $table->string('mat_titular')->nullable()->comment('Apellido materno titular');
            $table->string('p_nom_titular')->nullable()->comment('Primer nombre titular');
            $table->string('s_nombre_titular')->nullable()->comment('Segundo nombre titular');
            $table->string('carnet_tit')->nullable()->comment('Carnet numero titular');
            $table->string('num_com_tit')->nullable()->comment('complemento del numero de carnet titular');
            $table->date('fec_fail_tit')->nullable()->comment('Fecha de fallecimiento titular');
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
        Schema::dropIfExists('aid_contribution_copy_payroll_senasirs');
    }
}