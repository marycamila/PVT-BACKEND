<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTmpCopyDataSenasirsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tmp_copy_data_senasirs', function (Blueprint $table) {
            $table->id();
            $table->integer('id_person_senasir')->unique();
            $table->string('matricula_titular')->comment('Matricula titular');
            $table->string('mat_dh')->nullable()->comment('Matricula derechohabiente');
            $table->string('carnet')->nullable()->comment('Carnet numero');
            $table->string('num_com')->nullable()->comment('complemento del numero de carnet');
            $table->string('concat_carnet_num_com')->nullable()->comment('carnet y complemento de carnet concat');
            $table->string('paterno')->nullable()->comment('Apellido paterno');
            $table->string('materno')->nullable()->comment('Apellido materno');
            $table->string('p_nombre')->comment('Primer nombre');
            $table->string('s_nombre')->nullable()->comment('Segundo nombre');
            $table->string('ap_casada')->nullable()->comment('Apellido de casada');
            $table->date('fecha_nacimiento')->comment('Fecha de nacimiento');
            $table->string('lugar_nacimiento')->nullable()->comment('Lugar de nacimiento');
            $table->string('clase_renta')->comment('Clase de renta');
            $table->string('pat_titular')->nullable()->comment('Apellido paterno titular');
            $table->string('mat_titular')->nullable()->comment('Apellido materno titular');
            $table->string('p_nom_titular')->nullable()->comment('Primer nombre titular');
            $table->string('s_nombre_titular')->nullable()->comment('Segundo nombre titular');
            $table->string('ap_casada_titular')->nullable()->comment('Apellido de casada titular');
            $table->string('carnet_tit')->nullable()->comment('Carnet numero titular');
            $table->string('num_com_tit')->nullable()->comment('complemento del numero de carnet titular');
            $table->string('concat_carnet_num_com_tit')->nullable()->comment('carnet y complemento de carnet concat');
            $table->date('fec_fail_tit')->nullable()->comment('Fecha de fallecimiento titular');
            $table->string('lugar_nacimiento_tit')->nullable()->comment('Lugar de nacimiento titular');
            $table->enum('state', ['accomplished','unrealized'])->default('unrealized')->comment('Estadosi fue encontrado o no encontrado');
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
        Schema::dropIfExists('tmp_copy_data_senasirs');
    }
}
