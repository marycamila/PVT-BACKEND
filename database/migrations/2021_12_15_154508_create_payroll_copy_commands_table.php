<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePayrollCopyCommandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('db_aux')->create('payroll_copy_commands', function (Blueprint $table) {
            $table->id();
            $table->string('uni')->comment('Unidad');
            $table->integer('desg')->comment('Desglose');
            $table->integer('mes')->comment('Mes');
            $table->integer('a_o')->comment('Año');
            $table->string('car')->comment('Carnet');
            $table->string('car_formato')->nullable()->comment('Carnet formateado');
            $table->string('pat')->nullable()->comment('Apellido paterno');
            $table->string('mat')->nullable()->comment('Apellido materno');
            $table->string('apes')->nullable()->comment('Apellido esposo');
            $table->string('nom')->nullable()->comment('Primer nombre');
            $table->string('nom2')->nullable()->comment('Segundo nombre');
            $table->string('eciv')->comment('Estado civil');
            $table->string('niv')->comment('Nivel jerarquico');
            $table->string('gra')->comment('Grado');
            $table->string('sex')->comment('Género');
            $table->string('sue')->comment('Sueldo');
            $table->decimal('sue_formato', 13, 2)->nullable()->comment('Sueldo formateado');            
            $table->string('cat')->comment('Bono antiguedad');
            $table->decimal('cat_formato', 13, 2)->nullable()->comment('Bono antiguedad formateado');
            $table->string('est')->comment('Bono estudio');
            $table->decimal('est_formato', 13, 2)->nullable()->comment('Bono estudio formateado');
            $table->string('carg')->comment('Bono cargo');
            $table->decimal('carg_formato', 13, 2)->nullable()->comment('Bono cargo formateado');
            $table->string('fro')->comment('Bono frontera');
            $table->decimal('fro_formato', 13, 2)->nullable()->comment('Bono frontera formateado');
            $table->string('ori')->comment('Bono oriente');
            $table->decimal('ori_formato', 13, 2)->nullable()->comment('Bono oriente formateado');
            $table->string('bseg')->comment('Bono seguridad');
            $table->decimal('bseg_formato', 13, 2)->nullable()->comment('Bono seguridad formateado');
            $table->string('gan')->comment('Ganado');
            $table->decimal('gan_formato', 13, 2)->nullable()->comment('Ganado formateado');
            $table->string('mus')->comment('Total');
            $table->decimal('mus_formato', 13, 2)->nullable()->comment('Total formateado');
            $table->string('lpag')->comment('Liquido pagado');
            $table->decimal('lpag_formato', 13, 2)->nullable()->comment('Liquido pagado formateado');
            $table->string('nac')->comment('Fecha de nacimiento');
            $table->date('nac_formato')->nullable()->comment('Fecha de nacimiento formateado');
            $table->string('ing')->comment('Fecha de ingreso');
            $table->date('ing_formato')->nullable()->comment('Fecha de ingreso formateado');
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
        Schema::dropIfExists('payroll_copy_commands');
    }
}
