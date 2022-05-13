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
        Schema::create('import_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->unsigned()->comment('Id del usuario');
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('payroll_commands_id')->unsigned()->comment('Id planilla Comando');
            $table->foreign('payroll_commands_id')->references('id')->on('payroll_commands');
            $table->unsignedBigInteger('payroll_senasirs_id')->unsigned()->comment('Id planilla SENASIR');
            $table->foreign('payroll_senasirs_id')->references('id')->on('payroll_senasirs');
            $table->unsignedBigInteger('record_types_id')->unsigned();
            $table->foreign('record_types_id')->references('id')->on('record_types');
            $table->unsignedBigInteger('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles');
            $table->string('action')->comment('Descripción de la acción');
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
        Schema::dropIfExists('import_records');
    }
};
