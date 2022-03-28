<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnUserTypeRoleUserPermission extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('description')->nullable();
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('description')->nullable();

        });

        Schema::table('role_user', function (Blueprint $table) {
            $table->string('user_type')->nullable();
            //$table->primary(['user_id', 'role_id', 'user_type']);
        });


        Schema::table('user_permissions', function (Blueprint $table) {
            $table->string('user_type')->nullable();
            //$table->primary(['user_id', 'permission_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
