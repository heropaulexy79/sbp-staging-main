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
        Schema::table('lessons', function (Blueprint $table) {
            //
            $table->string('slug', 100)->after('title')->nullable();
        });

        Schema::table('courses', function (Blueprint $table) {
            //
            $table->string('slug', 100)->after('title')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lessons', function (Blueprint $table) {
            //
            $table->dropColumn('slug');
        });

        Schema::table('courses', function (Blueprint $table) {
            //
            $table->dropColumn('slug');
        });
    }
};
