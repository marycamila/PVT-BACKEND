<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionCopyComandsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contribution_copy_comands', function (Blueprint $table) {
            $table->id();
            $table->string('uni');
            $table->string('desg');
            $table->string('mes');
            $table->string('a_o');
            $table->string('car');
            $table->string('pat')->nullable();
            $table->string('mat')->nullable();
            $table->string('apes')->nullable();
            $table->string('nom');
            $table->string('nom2')->nullable();
            $table->string('eciv');
            $table->string('niv');
            $table->string('gra');
            $table->string('sex');
            $table->string('sue');
            $table->string('cat');
            $table->string('est');
            $table->string('carg');
            $table->string('fro');
            $table->string('ori');
            $table->string('bserg');
            $table->string('gan');
            $table->string('mus');
            $table->string('lpag');
            $table->string('nac');
            $table->string('ing');
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
        Schema::dropIfExists('contribution_copy_comands');
    }
}
