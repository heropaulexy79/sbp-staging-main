<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
            $table->dateTimeTz('next_billing_date')->nullable();
            $table->timestampTz('billed_at')->nullable();
            $table->enum('plan', ['starter', 'enterprise']);
            $table->enum('status', ['active', 'inactive']);
            $table->foreignId('payment_method_id')->nullable()->constrained('payment_methods')->cascadeOnDelete()->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            //
            $table->dropColumn('next_billing_date');
            $table->dropColumn('billed_at');
            $table->dropColumn('plan');
            $table->dropColumn('status');
            $table->dropConstrainedForeignId('payment_method_id');
        });
    }
};
