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
        Schema::create('market', function (Blueprint $table) {
            $table->id();
            $table->foreignId("item_id")->nullable(false)->constrained("items");
            $table->foreignId("client_id")->nullable(false)->constrained("clients");
            $table->bigInteger("price")->nullable(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('market');
    }
};
