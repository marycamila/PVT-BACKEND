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
        Schema::create('payroll_commands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('affiliate_id')->unsigned()->comment('Id del afiliado titular');
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->unsignedBigInteger('unit_id')->comment('Unidad');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->unsignedBigInteger('breakdown_id')->default(0)->comment('Desglose');
            $table->foreign('breakdown_id')->references('id')->on('breakdowns');
            $table->unsignedBigInteger('category_id')->comment('Categoría');
            $table->foreign('category_id')->references('id')->on('categories');
            $table->integer('month_p')->comment('Mes');
            $table->integer('year_p')->comment('Año');
            $table->string('identity_card')->comment('Carnet');
            $table->string('last_name')->nullable()->comment('Apellido paterno');
            $table->string('mothers_last_name')->nullable()->comment('Apellido materno');
            $table->string('surname_husband')->nullable()->comment('Apellido esposo');
            $table->string('first_name')->comment('Primer nombre');
            $table->string('second_name')->nullable()->comment('Segundo nombre');
            $table->string('civil_status')->comment('Estado civil');
            $table->unsignedBigInteger('hierarchy_id')->comment('Nivel jerarquico');
            $table->foreign('hierarchy_id')->references('id')->on('hierarchies');
            $table->unsignedBigInteger('degree_id')->comment('Grado');
            $table->foreign('degree_id')->references('id')->on('degrees');
            $table->string('gender')->comment('Género');
            $table->decimal('base_wage', 13, 2)->comment('Sueldo');            
            $table->decimal('seniority_bonus', 13, 2)->comment('Bono antiguedad');
            $table->decimal('study_bonus', 13, 2)->comment('Bono estudio');
            $table->decimal('position_bonus', 13, 2)->comment('Bono cargo');
            $table->decimal('border_bonus', 13, 2)->comment('Bono frontera');
            $table->decimal('east_bonus', 13, 2)->comment('Bono oriente');
            $table->decimal('public_security_bonus', 13, 2)->comment('Bono seguridad');
            $table->decimal('gain', 13, 2)->comment('Total ganado');
            $table->decimal('total', 13, 2)->comment('Total aporte');
            $table->decimal('payable_liquid', 13, 2)->comment('Liquido pagado');
            $table->date('birth_date')->comment('Fecha de nacimiento');
            $table->date('date_entry')->comment('Fecha de ingreso');
            $table->enum('affiliate_type', ['REGULAR', 'NUEVO'])->default('REGULAR')->comment('Afiliado regular o nuevo');            
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
        Schema::dropIfExists('payroll_commands');
    }
};
