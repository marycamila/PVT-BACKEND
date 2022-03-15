<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExtensionDblink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("CREATE EXTENSION IF NOT EXISTS dblink;");
        DB::connection('db_aux')->select("CREATE EXTENSION IF NOT EXISTS dblink;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP  extension dblink;");
        DB::connection('db_aux')->select("DROP  extension dblink;");
    }
}
