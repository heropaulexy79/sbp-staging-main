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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('auth_code')->nullable(false);
            $table->string('country');
            $table->string('bank')->nullable(true);
            $table->string('first_six');
            $table->string('last_four');
            $table->string('card_type');
            $table->boolean('reusable');
            $table->string('exp');
            $table->string('account_name');
            $table->foreignId('organisation_id')->constrained('organisations')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('email_address');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
