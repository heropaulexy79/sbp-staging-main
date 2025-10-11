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
        // I added the extra s cause i am renaming to subscription in a previous migration
        Schema::create('billing_historiess', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_ref')->unique();
            $table->float('amount');
            $table->string('description');
            $table->string('provider')->default('PAYSTACK');
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // I added the extra s cause i am renaming to subscription in a previous migration
        Schema::dropIfExists('billing_historiess');
    }
};
