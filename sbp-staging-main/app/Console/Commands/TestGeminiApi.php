<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TestGeminiApi extends Command
{
    protected $signature = 'rag:test-gemini {--test-embedding : Test embedding generation} {--test-content : Test content generation}';
    protected $description = 'Test Gemini API connections and responses';

    public function handle()
    {
        $this->info('Testing Gemini API...');
        
        $apiKey = config('services.gemini.api_key');
        $model = config('services.gemini.model', 'gemini-1.5-flash');
        
        if (!$apiKey) {
            $this->error('❌ GEMINI_API_KEY is not configured');
            return 1;
        }
        
        $this->info("✓ API Key configured: " . substr($apiKey, 0, 10) . "...");
        $this->info("✓ Model: {$model}");
        
        // Test embedding generation
        if ($this->option('test-embedding') || $this->option('test-content')) {
            $this->testEmbeddingGeneration($apiKey);
        }
        
        // Test content generation
        if ($this->option('test-content')) {
            $this->testContentGeneration($apiKey, $model);
        }
        
        // If no specific tests requested, run both
        if (!$this->option('test-embedding') && !$this->option('test-content')) {
            $this->testEmbeddingGeneration($apiKey);
            $this->testContentGeneration($apiKey, $model);
        }
        
        return 0;
    }
    
    private function testEmbeddingGeneration(string $apiKey)
    {
        $this->info("\n🔍 Testing Embedding Generation...");
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={$apiKey}", [
                'model' => 'models/text-embedding-004',
                'content' => [
                    'parts' => [
                        ['text' => 'Test embedding generation']
                    ]
                ]
            ]);
            
            $this->info("Status: " . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                $embedding = $data['embedding']['values'] ?? [];
                $this->info("✓ Embedding generated successfully");
                $this->info("✓ Embedding dimension: " . count($embedding));
            } else {
                $this->error("❌ Embedding generation failed");
                $this->error("Response: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Embedding test failed: " . $e->getMessage());
        }
    }
    
    private function testContentGeneration(string $apiKey, string $model)
    {
        $this->info("\n📝 Testing Content Generation...");
        
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}", [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => 'Write a short paragraph about machine learning.']
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'maxOutputTokens' => 100,
                ]
            ]);
            
            $this->info("Status: " . $response->status());
            
            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                $this->info("✓ Content generated successfully");
                $this->info("✓ Generated text: " . substr($content, 0, 100) . "...");
            } else {
                $this->error("❌ Content generation failed");
                $this->error("Response: " . $response->body());
            }
            
        } catch (\Exception $e) {
            $this->error("❌ Content generation test failed: " . $e->getMessage());
        }
    }
}
