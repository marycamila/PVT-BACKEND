<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToAidContributions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aid_contributions', function (Blueprint $table) {
            $table->enum('affiliate_rent_class', ['VEJEZ', 'VIUDEDAD'])->default('VEJEZ')->comment('Tipo de Afiliado que realizo el Aporte');
            $table->nullableMorphs('aid_contributionable'); // Campo para contribuiciones de aportes directos y complemento economico
            $table->dropColumn('mortuary_aid');
            $table->dropColumn('affiliate_contribution');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('aid_contributions', function (Blueprint $table) {
            //
        });
    }
}
