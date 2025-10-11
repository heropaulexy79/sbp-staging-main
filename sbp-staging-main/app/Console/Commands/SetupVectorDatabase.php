<?php

namespace App\Console\Commands;

use App\Services\HybridRagService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SetupVectorDatabase extends Command
{
    protected $signature = 'rag:setup-vectors';
    protected $description = 'Set up the vector database and sync existing knowledge base entries';

    public function handle()
    {
        $this->info('Setting up vector database...');
        
        try {
            // Check if libSQL connection is working
            $this->info('Testing libSQL connection...');
            DB::connection('libsql')->getPdo();
            $this->info('✓ libSQL connection successful');
            
            // Check if MySQL connection is working
            $this->info('Testing MySQL connection...');
            DB::connection('mysql')->getPdo();
            $this->info('✓ MySQL connection successful');
            
            // Check if Gemini API is configured
            $apiKey = config('services.gemini.api_key');
            if (!$apiKey) {
                $this->error('❌ GEMINI_API_KEY is not configured in config/services.php');
                return 1;
            }
            $this->info('✓ Gemini API key configured');
            
            // Run migrations for libSQL
            $this->info('Running libSQL migrations...');
            $this->call('migrate', ['--database' => 'libsql']);
            $this->info('✓ libSQL migrations completed');
            
            // Sync existing knowledge base entries
            $this->info('Syncing existing knowledge base entries to vector database...');
            $ragService = app(HybridRagService::class);
            $synced = $ragService->syncKnowledgeBaseToVectors();
            $this->info("✓ Synced {$synced} entries to vector database");
            
            // Display status
            $knowledgeCount = DB::table('knowledge_base')->count();
            $vectorCount = DB::connection('libsql')->table('vector_embeddings')->count();
            
            $this->info("\n📊 Database Status:");
            $this->info("  MySQL knowledge_base entries: {$knowledgeCount}");
            $this->info("  libSQL vector_embeddings entries: {$vectorCount}");
            
            if ($knowledgeCount === $vectorCount) {
                $this->info("  ✓ Databases are in sync");
            } else {
                $this->warn("  ⚠️  Databases are not in sync");
            }
            
            $this->info("\n🎉 Vector database setup completed successfully!");
            $this->info("You can now use the RAG system for AI-powered content generation.");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Setup failed: " . $e->getMessage());
            return 1;
        }
    }
}
