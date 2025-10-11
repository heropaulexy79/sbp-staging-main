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
    //     // Create vector embeddings table in libSQL
    //     Schema::connection('libsql')->create('vector_embeddings', function (Blueprint $table) {
    //         $table->id();
    //         $table->unsignedBigInteger('knowledge_base_id'); // Reference to MySQL knowledge_base table
    //         $table->string('title');
    //         $table->text('content');
    //         $table->enum('source_type', ['course', 'lesson', 'quiz', 'external']);
    //         $table->unsignedBigInteger('source_id')->nullable();
    //         $table->text('keywords')->nullable();
            
    //         // Vector embedding column (stored as JSON for Laravel compatibility)
    //         $table->json('embedding')->nullable();
            
    //         $table->timestamps();
            
    //         $table->index('knowledge_base_id');
    //         $table->index(['source_type', 'source_id']);
    //         $table->index('title');
    //     });

    //     // Create vector index using raw SQL for libSQL vector operations
    //     DB::connection('libsql')->statement('
    //         CREATE INDEX IF NOT EXISTS idx_vector_embedding 
    //         ON vector_embeddings 
    //         USING VECTOR_COSINE(embedding)
    //     ');
    // }
    public function up(): void
{
    if (!Schema::connection('libsql')->hasTable('vector_embeddings')) {
        Schema::connection('libsql')->create('vector_embeddings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('knowledge_base_id');
            $table->string('title');
            $table->text('content');
            $table->enum('source_type', ['course', 'lesson', 'quiz', 'external']);
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('keywords')->nullable();
            $table->json('embedding')->nullable();
            $table->timestamps();

            $table->index('knowledge_base_id');
            $table->index(['source_type', 'source_id']);
            $table->index('title');
        });

        // Create vector index if it doesn’t exist
        DB::connection('libsql')->statement('
            CREATE INDEX IF NOT EXISTS idx_vector_embedding 
            ON vector_embeddings 
            USING VECTOR_COSINE(embedding)
        ');
    }
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('libsql')->dropIfExists('vector_embeddings');
    }
};
