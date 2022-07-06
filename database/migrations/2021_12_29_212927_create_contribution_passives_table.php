<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionPassivesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
            Schema::create('contribution_passives', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');  // id usuario
            $table->foreign('user_id')->references('id')->on('users');
            $table->unsignedBigInteger('affiliate_id')->unsigned();
            $table->foreign('affiliate_id')->references('id')->on('affiliates');
            $table->date('month_year')->comment('Periodo del aporte');
            $table->unique(['affiliate_id', 'month_year']);
            $table->decimal('quotable',13,2)->default(0)->comment('cotizable');
            $table->decimal('rent_pension',13,2)->default(0)->comment('Monto de Renta o pension del sec pasivo');
            $table->decimal('dignity_rent',13,2)->default(0)->comment('Renta dignidad');
            $table->decimal('interest',13,2)->default(0)->comment('Interes');
            $table->decimal('total',13,2)->default(0)->comment('Total aporte');
            $table->enum('affiliate_rent_class', ['VEJEZ', 'VIUDEDAD'])->default('VEJEZ')->comment('Tipo de Afiliado que realizo el Aporte');
            $table->unsignedBigInteger('contribution_state_id')->unsigned();
            $table->foreign('contribution_state_id')->references('id')->on('contribution_states');
            $table->nullableMorphs('contributionable'); // Campo para contribuiciones de aportes directos y complemento economico
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
        Schema::dropIfExists('contribution_passives');
    }
}
