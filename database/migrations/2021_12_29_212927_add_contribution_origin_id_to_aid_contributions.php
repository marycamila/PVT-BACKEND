<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddContributionOriginIdToAidContributions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('aid_contributions', function (Blueprint $table) {
            $table->foreign('contribution_origin_id')->references('id')->on('contribution_origins');
            $table->unsignedBigInteger('contribution_origin_id')->nullable(); // id del bien inmueble
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
