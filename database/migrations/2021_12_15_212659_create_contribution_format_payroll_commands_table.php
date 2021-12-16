<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionFormatPayrollCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_format_payroll_commands', function (Blueprint $table) {
            $table->id();
            $table->integer('uni')->comment('Unidad');
            $table->integer('desg')->default(0)->comment('Desglose');
            $table->integer('mes')->comment('Mes');
            $table->integer('a_o')->comment('Año');
            $table->string('car')->comment('Carnet');
            $table->string('pat')->nullable()->comment('Apellido paterno');
            $table->string('mat')->nullable()->comment('Apellido materno');
            $table->string('apes')->nullable()->comment('Apellido esposo');
            $table->string('nom')->comment('Primer nombre');
            $table->string('nom2')->nullable()->comment('Segundo nombre');
            $table->string('eciv')->comment('Estado civil');
            $table->string('niv')->comment('Nivel jerarquico');
            $table->string('gra')->comment('Grado');
            $table->string('sex')->comment('Género');
            $table->decimal('sue', 13, 2)->comment('Sueldo');
            $table->decimal('cat', 13, 2)->comment('Bono antiguedad');
            $table->decimal('est', 13, 2)->comment('Bono estudio');
            $table->decimal('carg', 13, 2)->comment('Bono cargo');
            $table->decimal('fro', 13, 2)->comment('Bono frontera');
            $table->decimal('ori', 13, 2)->comment('Bono oriente');
            $table->decimal('bseg', 13, 2)->comment('Bono seguridad');
            $table->decimal('gan', 13, 2)->comment('Ganancia');
            $table->decimal('mus', 13, 2)->comment('total');
            $table->decimal('lpag', 13, 2)->comment('Liquido pagado');
            $table->date('nac')->comment('Fecha de nacimiento');
            $table->date('ing')->comment('Fecha de ingreso');
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
        Schema::dropIfExists('contribution_format_payroll_commands');
    }
}
