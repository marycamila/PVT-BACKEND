<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionCopyPayrollCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_copy_payroll_commands', function (Blueprint $table) {
            $table->id();
            $table->string('uni')->comment('Unidad');
            $table->string('desg')->comment('Desglose');
            $table->string('mes')->comment('Mes');
            $table->string('a_o')->comment('Año');
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
            $table->string('sue')->comment('Sueldo');
            $table->string('cat')->comment('Bono antiguedad');
            $table->string('est')->comment('Bono estudio');
            $table->string('carg')->comment('Bono cargo');
            $table->string('fro')->comment('Bono frontera');
            $table->string('ori')->comment('Bono oriente');
            $table->string('bseg')->comment('Bono seguridad');
            $table->string('gan')->comment('Ganancia');
            $table->string('mus')->comment('total');
            $table->string('lpag')->comment('Liquido pagado');
            $table->string('nac')->comment('Fecha de nacimiento');
            $table->string('ing')->comment('Fecha de ingreso');
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
        Schema::dropIfExists('contribution_copy_payroll_commands');
    }
}
