<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            //
            $table->string('exp_month');
            $table->string('exp_year');
            $table->string('account_name')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            //
            $table->dropColumn('exp_month');
            $table->dropColumn('exp_year');
            // $table->dropColumn('exp');
            $table->string('account_name')->nullable(false);
        });
    }
};
