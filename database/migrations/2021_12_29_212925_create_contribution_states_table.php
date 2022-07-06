<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->comment('Estados de la contribución pagado,en proceso y devuelto');
            $table->string('description')->comment('Descripcion del estado de la contribución');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contribution_states');
    }
}
