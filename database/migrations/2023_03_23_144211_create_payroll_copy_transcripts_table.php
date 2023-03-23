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
        Schema::connection('db_aux')->create('payroll_copy_transcripts', function (Blueprint $table) {
            $table->id();
            $table->integer('num')->comment('Numero de registro');
            $table->string('obs')->comment('observacion');
            $table->string('uni')->comment('Unidad');
            $table->integer('mes')->comment('Mes');
            $table->integer('a_o')->comment('Año');
            $table->string('car')->comment('Carnet');
            $table->string('pat')->nullable()->comment('Apellido paterno');
            $table->string('mat')->nullable()->comment('Apellido materno');
            $table->string('nom')->nullable()->comment('Primer nombre');
            $table->string('nom2')->nullable()->comment('Segundo nombre');
            $table->decimal('gan', 13, 2)->comment('Total ganado');
            $table->decimal('mus', 13, 2)->comment('Aporte Muserpol');
            $table->string('niv')->comment('Nivel jerarquico');
            $table->string('gra')->comment('Grado');
            $table->decimal('sue', 13, 2)->comment('Sueldo');
            $table->decimal('cat', 13, 2)->comment('Bono antiguedad');
            $table->decimal('est', 13, 2)->comment('Bono estudio');
            $table->decimal('carg', 13, 2)->comment('Bono cargo');
            $table->decimal('fro', 13, 2)->comment('Bono frontera');
            $table->decimal('ori', 13, 2)->comment('Bono oriente');
            $table->date('nac')->nullable()->comment('Fecha de nacimiento');
            $table->date('ing')->nullable()->comment('Fecha de ingreso');
            $table->unsignedBigInteger('affiliate_id')->unsigned()->nullable()->comment('Id del afiliado titular');
            $table->enum('state', ['accomplished','unrealized'])->default('unrealized')->comment('Estado si fue encontrado o no encontrado');
            $table->string('criteria')->nullable()->comment('creitetio de identificacion del afiliado');
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
        Schema::dropIfExists('payroll_copy_transcripts');
    }
};
