<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionOriginsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_origins', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique()->comment('Nombre');
            $table->string('shortened')->unique()->comment('Nombre Corto');
            $table->string('description')->comment('descripción');
            $table->unsignedBigInteger('pension_entity_id')->comment('id de pension'); // id de la pension
            $table->foreign('pension_entity_id')->references('id')->on('pension_entities');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contribution_origins');
    }
}
