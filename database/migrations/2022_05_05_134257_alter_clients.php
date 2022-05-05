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
        Schema::table('clients', function (Blueprint $table)
        {
            $table->unique('login', 'login_unique');
            $table->unique('email', 'email_unique');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('clients', function (Blueprint $table)
        {
            $table->dropUnique('login_unique');
            $table->dropUnique('email_unique');
            $table->dropColumn('remember_token');
            $table->dropColumn(['created_at', 'updated_at']);
        });
    }
};
