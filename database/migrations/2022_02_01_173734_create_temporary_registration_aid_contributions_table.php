<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTemporaryRegistrationAidContributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temporary_registration_aid_contributions', function (Blueprint $table) {
            $table->id();
            $table->integer('contribution_aid_id')->comment('contribucion senasir');
            $table->integer('user_id')->comment('Usuario');
            $table->integer('affiliate_id')->comment('Afiliado');
            $table->string('month_year')->comment('Perido');
            $table->decimal('quotable', 13, 2)->comment('cotizable');
            $table->decimal('rent', 13, 2)->comment('Liquido Pagable');
            $table->decimal('dignity_rent', 13, 2)->comment('Renta Digniodad');
            $table->decimal('interest', 13, 2)->comment('Interes');
            $table->decimal('total', 13, 2)->comment('Descuento Muserpol');
            $table->boolean('valid')->nullable();
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
        Schema::dropIfExists('temporary_registration_aid_contributions');
    }
}
