<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    // public function up(): void
    // {
    //     // Create resources table in libSQL for vector storage
    //     Schema::connection('libsql')->create('resources', function (Blueprint $table) {
    //         $table->uuid('id')->primary();
    //         $table->unsignedBigInteger('course_id')->nullable();
    //         $table->string('title');
    //         $table->longText('content');
    //         $table->json('embedding')->nullable(); // Vector embedding (1536 dim for OpenAI)
    //         $table->json('metadata')->nullable();
    //         $table->timestamps();
            
    //         // Indexes for performance
    //         $table->index('course_id');
    //         $table->index('title');
    //         $table->index('created_at');
    //     });

    //     // Create vector index using raw SQL for libSQL vector operations
    //     // This enables cosine similarity search on the embedding column
    //     try {
    //         DB::connection('libsql')->statement('
    //             CREATE INDEX IF NOT EXISTS idx_resources_embedding 
    //             ON resources 
    //             USING VECTOR_COSINE(embedding)
    //         ');
    //     } catch (\Exception $e) {
    //         // Fallback: create a regular index if vector extensions aren't available
    //         DB::connection('libsql')->statement('
    //             CREATE INDEX IF NOT EXISTS idx_resources_embedding 
    //             ON resources (embedding)
    //         ');
    //     }
    // }
    public function up(): void
{
    // Safely check if table already exists
    if (!Schema::connection('libsql')->hasTable('resources')) {

        Schema::connection('libsql')->create('resources', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('course_id')->nullable();
            $table->string('title');
            $table->longText('content');
            $table->json('embedding')->nullable(); // Vector embedding (1536 dims for OpenAI)
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('course_id');
            $table->index('title');
            $table->index('created_at');
        });

        // Create vector index (libSQL supports VECTOR_COSINE if vector extension is enabled)
        try {
            DB::connection('libsql')->statement('
                CREATE INDEX IF NOT EXISTS idx_resources_embedding 
                ON resources 
                USING VECTOR_COSINE(embedding)
            ');
        } catch (\Throwable $e) {
            // Fallback if VECTOR_COSINE is not supported
            DB::connection('libsql')->statement('
                CREATE INDEX IF NOT EXISTS idx_resources_embedding 
                ON resources (embedding)
            ');
        }
    }
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('libsql')->dropIfExists('resources');
    }
};
