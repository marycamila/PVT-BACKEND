<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCopyPersonSenasirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db_aux')->create('copy_person_senasirs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_person_senasir')->unique();
            $table->string('matricula_tit')->comment('Matricula titular');
            $table->string('carnet_tit')->nullable()->comment('Carnet numero titular');
            $table->string('num_com_tit')->nullable()->comment('complemento del numero de carnet titular');
            $table->string('concat_carnet_num_com_tit')->nullable()->comment('carnet y complemento de carnet concat');
            $table->string('p_nom_tit')->nullable()->comment('Primer nombre titular');
            $table->string('s_nombre_tit')->nullable()->comment('Segundo nombre titular');
            $table->string('paterno_tit')->nullable()->comment('Apellido paterno titular');
            $table->string('materno_tit')->nullable()->comment('Apellido materno titular');
            $table->string('ap_casada_tit')->nullable()->comment('Apellido de casada titular');
            $table->date('fecha_nacimiento_tit')->comment('Fecha de nacimiento');
            $table->string('genero_tit')->nullable()->comment('Genero del titular');
            $table->date('fec_fail_tit')->nullable()->comment('Fecha de fallecimiento titular');
            $table->string('clase_renta_tit')->comment('Clase de renta');

            $table->string('mat_dh')->nullable()->comment('Matricula derechohabiente');
            $table->string('carnet_dh')->nullable()->comment('Carnet numero');
            $table->string('num_com_dh')->nullable()->comment('complemento del numero de carnet');
            $table->string('concat_carnet_num_com_dh')->nullable()->comment('carnet y complemento de carnet concat');
            $table->string('p_nombre_dh')->comment('Primer nombre');
            $table->string('s_nombre_dh')->nullable()->comment('Segundo nombre');
            $table->string('paterno_dh')->nullable()->comment('Apellido paterno');
            $table->string('materno_dh')->nullable()->comment('Apellido materno');
            $table->string('ap_casada_dh')->nullable()->comment('Apellido de casada');
            $table->date('fecha_nacimiento_dh')->comment('Fecha de nacimiento');
            $table->string('genero_dh')->nullable()->comment('Genero derechohabiente');
            $table->date('fec_fail_dh')->nullable()->comment('Fecha de fallecimiento derechohabiente');
            $table->string('clase_renta_dh')->comment('Clase de renta');
            $table->enum('state', ['accomplished','unrealized'])->default('unrealized')->comment('Estado si fue encontrado o no encontrado');
            $table->string('observacion')->nullable()->comment('Observacion del registro');
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
        Schema::connection('db_aux')->dropIfExists('copy_person_senasirs');
    }
}
