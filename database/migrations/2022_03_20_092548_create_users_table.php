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
        Schema::create('trans_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
        });

        Schema::create('qualities', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
        });

        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('description')->default('');
            $table->bigInteger('price')->default(0);
            $table->string('picture')->nullable(false);
        });


        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('login', 256)->nullable(false);
            $table->string('password', 256)->nullable(false);
            $table->string('email', 256)->nullable(true);
            $table->integer('permission')->nullable(false);
            $table->bigInteger('balance')->default(0);

            $table->foreign('permission')->references('id')->on('permissions');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->integer('type')->nullable(false);
            $table->bigInteger('client_id')->nullable(false);
            $table->bigInteger('accrual')->default(0);

            $table->foreign('type')->references('id')->on('trans_types');
            $table->foreign('client_id')->references('id')->on('clients');
        });

        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('description')->default('');
            $table->bigInteger('price')->default('0');
            $table->integer('quality')->nullable(false);
            $table->string('picture')->nullable(false);

            $table->foreign('quality')->references('id')->on('qualities');
        });

        Schema::create('case_item', function (Blueprint $table) {
            $table->bigInteger('case_id');
            $table->bigInteger('item_id');
            $table->float('chance')->default(0);

            $table->primary(['case_id', 'item_id']);
            $table->foreign('case_id')->references('id')->on('cases');
            $table->foreign('item_id')->references('id')->on('items');
        });

        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->nullable(false);
            $table->bigInteger('item_id')->nullable(false);

            $table->foreign('client_id')->references('id')->on('clients');
            $table->foreign('item_id')->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('trans_types');
        Schema::dropIfExists('inventories');
        Schema::dropIfExists('clients');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('case_item');
        Schema::dropIfExists('cases');
        Schema::dropIfExists('items');
        Schema::dropIfExists('qualities');
    }
};
