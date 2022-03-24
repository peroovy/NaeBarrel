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
        if (Schema::hasTable('trans_types')) {
            Schema::table('trans_types', function (Blueprint $table) {
                $table->rename('transaction_types');
            });
        }

        if (Schema::hasTable('cases')) {
            Schema::table('cases', function (Blueprint $table) {
                $table->string('description')->nullable(false)->change();
                $table->bigInteger('price')->nullable(false)->change();
            });
        }

        if (Schema::hasTable('clients')) {
            Schema::table('clients', function (Blueprint $table) {
                $table->bigInteger('balance')->nullable(false)->change();
                $table->dropForeign('clients_permission_foreign');
                $table->foreignId('permission')->change()->constrained('permissions')->nullOnDelete();
            });
        }

        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->bigInteger('accrual')->nullable(false)->change();
                $table->dropForeign('transactions_client_id_foreign');
                $table->dropForeign('transactions_type_foreign');
                $table->foreignId('client_id')->change()->constrained('clients')->nullOnDelete();
                $table->foreignId('type')->change()->constrained('transaction_types')->nullOnDelete();
            });
        }

        if (Schema::hasTable('items')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('description')->nullable(false)->change();
                $table->bigInteger('price')->nullable(false)->default(0)->change();
                $table->dropForeign('items_quality_foreign');
                $table->foreignId('quality')->change()->constrained('qualities')->nullOnDelete();
            });
        }

        if (Schema::hasTable('case_item')) {
            Schema::table('case_item', function (Blueprint $table) {
                $table->float('chance')->nullable(false)->change();
                $table->dropForeign('case_item_case_id_foreign');
                $table->dropForeign('case_item_item_id_foreign');
                $table->foreignId('case_id')->change()->constrained('cases')->cascadeOnDelete();
                $table->foreignId('item_id')->change()->constrained('items')->cascadeOnDelete();
            });
        }

        if (Schema::hasTable('inventories')) {
            Schema::table('inventories', function (Blueprint $table) {
                $table->dropForeign('inventories_client_id_foreign');
                $table->dropForeign('inventories_item_id_foreign');
                $table->foreignId('client_id')->change()->constrained('clients')->cascadeOnDelete();
                $table->foreignId('item_id')->change()->constrained('items')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_types', function (Blueprint $table) {
            $table->rename('trans_types');
        });

        Schema::table('cases', function (Blueprint $table) {
            $table->string('description')->nullable(true)->change();
            $table->bigInteger('price')->nullable(true)->change();
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->bigInteger('balance')->nullable(true)->change();
            $table->integer('permission')->change();
            $table->foreign('permission')->references('id')->on('permissions');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->bigInteger('accrual')->nullable(true)->change();
            $table->integer('type')->change();
            $table->foreign('type')->references('id')->on('trans_types');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->string('description')->nullable(true)->change();
            $table->bigInteger('price')->nullable(true)->default('0')->change();
            $table->integer('quality')->change();
            $table->foreign('quality')->references('id')->on('qualities');
        });

        Schema::table('case_item', function (Blueprint $table) {
            $table->float('chance')->nullable(true)->change();
            $table->bigInteger('case_id')->change();
            $table->bigInteger('item_id')->change();
            $table->foreign('case_id')->references('id')->on('cases');
            $table->foreign('item_id')->references('id')->on('items');
        });

        Schema::table('inventories', function (Blueprint $table) {
            $table->bigInteger('client_id')->change();
            $table->foreign('client_id')->references('id')->on('clients');
            $table->bigInteger('item_id')->change();
            $table->foreign('item_id')->references('id')->on('items');
        });
    }
};
