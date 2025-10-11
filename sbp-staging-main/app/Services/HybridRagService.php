<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Resource;

class HybridRagService
{
    private string $geminiApiKey;
    private string $geminiModel;

    public function __construct()
    {
        $this->geminiApiKey = config('services.gemini.api_key');
        $this->geminiModel = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Generate content using Gemini AI with a custom prompt
     */
    public function generateContent(string $prompt, array $options = []): string
    {
        try {
            $response = $this->generateWithGemini("Custom Generation", $prompt, $options);
            return $response;
        } catch (\Exception $e) {
            Log::error('Content generation failed', [
                'prompt' => substr($prompt, 0, 100) . '...',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate JSON content using Gemini AI with JSON mode
     */
    public function generateJsonContent(string $prompt, array $options = []): string
    {
        try {
            $response = $this->generateWithGemini("JSON Generation", $prompt, $options, true);
            return $response;
        } catch (\Exception $e) {
            Log::error('JSON content generation failed', [
                'prompt' => substr($prompt, 0, 100) . '...',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Generate lesson content using RAG with vector similarity search
     */
    public function generateLessonContent(string $title, int $courseId, array $options = []): string
    {
        try {
            Log::info('Starting lesson content generation', [
                'title' => $title,
                'course_id' => $courseId,
                'options' => $options
            ]);

            // 1. Generate embedding for the lesson title
            $titleEmbedding = $this->generateEmbedding($title);
            
            // 2. Find semantically similar content using libSQL vector search
            $relevantContent = $this->findSimilarContent($titleEmbedding, $courseId, 5);
            
            // 3. Add specific reference resources if provided
            if (!empty($options['reference_resources'])) {
                Log::info('Adding reference resources', [
                    'reference_resources' => $options['reference_resources']
                ]);
                $referenceContent = $this->getReferenceResources($options['reference_resources']);
                $relevantContent = array_merge($relevantContent, $referenceContent);
            }
            
            // 4. Prepare context for AI generation
            $context = $this->prepareContext($relevantContent, $courseId);
            
            // 5. Generate content using Gemini AI
            $generatedContent = $this->generateWithGemini($title, $context, $options);
            
            return $generatedContent;
            
        } catch (\Exception $e) {
            Log::error('RAG content generation failed', [
                'title' => $title,
                'course_id' => $courseId,
                'options' => $options,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Add knowledge base entry to both MySQL and libSQL
     */
    public function addKnowledgeBaseEntry(
        string $title,
        string $content,
        string $sourceType,
        ?int $sourceId = null,
        ?string $keywords = null
    ): bool {
        try {
            DB::beginTransaction();
            
            // 1. Add to MySQL knowledge_base table
            $knowledgeId = DB::table('knowledge_base')->insertGetId([
                'title' => $title,
                'content' => $content,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'keywords' => $keywords,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // 2. Generate embedding for the content
            $embedding = $this->generateEmbedding($content);
            
            // 3. Add to libSQL vector_embeddings table
            DB::connection('libsql')->table('vector_embeddings')->insert([
                'knowledge_base_id' => $knowledgeId,
                'title' => $title,
                'content' => $content,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'keywords' => $keywords,
                'embedding' => json_encode($embedding),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            DB::commit();
            return true;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to add knowledge base entry', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Search knowledge base using vector similarity
     */
    public function searchKnowledgeBase(string $query, int $limit = 5): array
    {
        try {
            // Generate embedding for the search query
            $queryEmbedding = $this->generateEmbedding($query);
            
            // Search using libSQL vector similarity
            $results = DB::connection('libsql')
                ->select("
                    SELECT knowledge_base_id, title, content, 
                           VECTOR_COSINE_SIMILARITY(embedding, ?) as similarity
                    FROM vector_embeddings 
                    WHERE VECTOR_COSINE_SIMILARITY(embedding, ?) > 0.7
                    ORDER BY similarity DESC
                    LIMIT ?
                ", [json_encode($queryEmbedding), json_encode($queryEmbedding), $limit]);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Vector search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Get specific reference resources by IDs
     */
    private function getReferenceResources(array $resourceIds): array
    {
        try {
            $resources = Resource::whereIn('id', $resourceIds)->get();
            
            return $resources->map(function ($resource) {
                return (object) [
                    'id' => $resource->id,
                    'title' => $resource->title,
                    'content' => $resource->content,
                    'similarity' => 1.0, // High similarity since explicitly selected
                    'source' => 'reference_resource'
                ];
            })->toArray();
            
        } catch (\Exception $e) {
            Log::error('Failed to get reference resources', [
                'resource_ids' => $resourceIds,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Find similar content using vector embeddings
     */
    private function findSimilarContent(array $embedding, int $courseId, int $limit = 5): array
    {
        try {
            $results = DB::connection('libsql')
                ->select("
                    SELECT knowledge_base_id, title, content, 
                           VECTOR_COSINE_SIMILARITY(embedding, ?) as similarity
                    FROM vector_embeddings 
                    WHERE VECTOR_COSINE_SIMILARITY(embedding, ?) > 0.7
                    ORDER BY similarity DESC
                    LIMIT ?
                ", [json_encode($embedding), json_encode($embedding), $limit]);
            
            return $results;
            
        } catch (\Exception $e) {
            Log::error('Vector similarity search failed', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Generate embedding using Gemini API
     */
    public function generateEmbedding(string $text): array
    {
        try {
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/text-embedding-004:embedContent?key={$this->geminiApiKey}", [
                'model' => 'models/text-embedding-004',
                'content' => [
                    'parts' => [
                        ['text' => $text]
                    ]
                ]
            ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['embedding']['values'] ?? [];
            }
            
            // Log the actual response for debugging
            Log::error('Embedding API response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'text' => substr($text, 0, 100) . '...'
            ]);
            
            throw new \Exception('Failed to generate embedding: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Embedding generation failed', [
                'text' => substr($text, 0, 100) . '...',
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Prepare context for AI generation
     */
    private function prepareContext(array $relevantContent, int $courseId): string
    {
        $context = "Relevant educational content:\n\n";
        
        // Separate reference resources from similar content
        $referenceResources = array_filter($relevantContent, fn($item) => $item->source === 'reference_resource');
        $similarContent = array_filter($relevantContent, fn($item) => $item->source !== 'reference_resource');
        
        // Add reference resources first (explicitly selected)
        if (!empty($referenceResources)) {
            $context .= "=== REFERENCE RESOURCES (Explicitly Selected) ===\n\n";
            foreach ($referenceResources as $item) {
                $title = is_string($item->title) ? $item->title : (string) $item->title;
                $content = is_string($item->content) ? $item->content : (string) $item->content;
                $context .= "Title: {$title}\n";
                $context .= "Content: {$content}\n";
                $context .= "Source: Reference Resource\n\n";
            }
        }
        
        // Add similar content
        if (!empty($similarContent)) {
            $context .= "=== SIMILAR CONTENT (AI Found) ===\n\n";
            foreach ($similarContent as $item) {
                $title = is_string($item->title) ? $item->title : (string) $item->title;
                $content = is_string($item->content) ? $item->content : (string) $item->content;
                $context .= "Title: {$title}\n";
                $context .= "Content: {$content}\n";
                $context .= "Relevance: " . round($item->similarity * 100, 1) . "%\n\n";
            }
        }
        
        // Add course-specific context if available
        $course = DB::table('courses')->where('id', $courseId)->first();
        if ($course) {
            $context .= "=== COURSE CONTEXT ===\n";
            $context .= "Course: {$course->title}\n";
            if ($course->description) {
                $context .= "Description: {$course->description}\n";
            }
        }
        
        return $context;
    }

    /**
     * Generate content using Gemini AI
     */
    private function generateWithGemini(string $title, string $context, array $options = [], bool $jsonMode = false): string
    {
        try {
            $prompt = $this->buildPrompt($title, $context, $options);
            
            $requestData = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => $options['temperature'] ?? 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => $options['max_tokens'] ?? 4000,
                ]
            ];

            // Add JSON mode if requested
            if ($jsonMode) {
                $requestData['generationConfig']['responseMimeType'] = 'application/json';
            }

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$this->geminiModel}:generateContent?key={$this->geminiApiKey}", $requestData);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
                
                // Clean up and format the HTML content
                return $this->formatHtmlContent($content);
            }
            
            // Log the actual response for debugging
            Log::error('Content generation API response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'title' => $title
            ]);
            
            throw new \Exception('Failed to generate content: ' . $response->body());
            
        } catch (\Exception $e) {
            Log::error('Content generation failed', [
                'title' => $title,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Build prompt for AI generation
     */
    private function buildPrompt(string $title, string $context, array $options): string
    {
        $prompt = "You are an expert educational content creator. Create comprehensive lesson content for the following topic:\n\n";
        $prompt .= "Lesson Title: {$title}\n\n";
        $prompt .= "Use the following context to create relevant, engaging, and educational content:\n\n";
        $prompt .= $context . "\n\n";
        $prompt .= "Requirements:\n";
        $prompt .= "- Create structured, easy-to-follow content in HTML format\n";
        $prompt .= "- Use proper HTML tags: <h1>, <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <br>\n";
        $prompt .= "- Include practical examples where relevant\n";
        $prompt .= "- Use clear, engaging language\n";
        $prompt .= "- Ensure content is appropriate for the educational context\n";
        $prompt .= "- Structure content with headings and paragraphs\n";
        $prompt .= "- Use bullet points or numbered lists for key concepts\n";
        $prompt .= "- Add emphasis with <strong> and <em> tags where appropriate\n";
        $prompt .= "- Use moderate spacing between sections - avoid excessive line breaks\n";
        $prompt .= "- Keep paragraphs concise and well-structured\n";
        
        if (!empty($options)) {
            $prompt .= "\nAdditional requirements:\n";
            foreach ($options as $key => $value) {
                // Handle array values properly
                if (is_array($value)) {
                    $value = implode(', ', $value);
                }
                $prompt .= "- {$key}: {$value}\n";
            }
        }
        
        $prompt .= "\nGenerate the lesson content in HTML format now. Start with a main heading and structure the content properly:";
        
        return $prompt;
    }

    /**
     * Format and clean up HTML content for the rich text editor
     */
    private function formatHtmlContent(string $content): string
    {
        // Remove any markdown formatting that might have been generated
        $content = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $content);
        $content = preg_replace('/\*(.*?)\*/', '<em>$1</em>', $content);
        
        // Convert markdown headers to HTML
        $content = preg_replace('/^### (.*$)/m', '<h3>$1</h3>', $content);
        $content = preg_replace('/^## (.*$)/m', '<h2>$1</h2>', $content);
        $content = preg_replace('/^# (.*$)/m', '<h1>$1</h1>', $content);
        
        // Convert markdown lists to HTML
        $content = preg_replace('/^\- (.*$)/m', '<li>$1</li>', $content);
        $content = preg_replace('/^\* (.*$)/m', '<li>$1</li>', $content);
        $content = preg_replace('/^\d+\. (.*$)/m', '<li>$1</li>', $content);
        
        // Wrap consecutive list items in ul tags
        $content = preg_replace('/(<li>.*<\/li>)/s', '<ul>$1</ul>', $content);
        
        // Normalize line breaks - replace multiple line breaks with single line breaks
        $content = preg_replace('/\n{3,}/', "\n\n", $content);
        
        // Split content into blocks based on double line breaks
        $blocks = preg_split('/\n\s*\n/', trim($content));
        $formattedBlocks = [];
        
        foreach ($blocks as $block) {
            $block = trim($block);
            if (empty($block)) continue;
            
            // Check if it's already an HTML element
            if (preg_match('/^<(h[1-6]|ul|ol|li|p|div)/i', $block)) {
                $formattedBlocks[] = $block;
            } else {
                // Wrap in paragraph if it's plain text
                $formattedBlocks[] = '<p>' . $block . '</p>';
            }
        }
        
        // Join blocks with single line breaks for moderate spacing
        $content = implode("\n", $formattedBlocks);
        
        // Clean up any empty paragraphs
        $content = preg_replace('/<p>\s*<\/p>/', '', $content);
        $content = preg_replace('/<p><\/p>/', '', $content);
        
        // Ensure proper HTML structure
        if (!str_contains($content, '<h1>') && !str_contains($content, '<h2>') && !str_contains($content, '<h3>')) {
            // If no headings found, wrap the first paragraph in h2
            $content = preg_replace('/^<p>(.*?)<\/p>/', '<h2>$1</h2>', $content, 1);
        }
        
        // Clean up any double wrapping
        $content = preg_replace('/<p><h([1-6])>/', '<h$1>', $content);
        $content = preg_replace('/<\/h([1-6])><\/p>/', '</h$1>', $content);
        
        // Remove excessive whitespace between elements
        $content = preg_replace('/>\s+</', '><', $content);
        
        // Add single line breaks between major elements for readability
        $content = preg_replace('/(<\/h[1-6]>)</', '$1' . "\n" . '<', $content);
        $content = preg_replace('/(<\/p>)</', '$1' . "\n" . '<', $content);
        $content = preg_replace('/(<\/ul>)</', '$1' . "\n" . '<', $content);
        $content = preg_replace('/(<\/ol>)</', '$1' . "\n" . '<', $content);
        
        return trim($content);
    }

    /**
     * Sync existing knowledge base entries to vector database
     */
    public function syncKnowledgeBaseToVectors(): int
    {
        $synced = 0;
        
        try {
            // Get all knowledge base entries that don't have vectors yet
            $entries = DB::table('knowledge_base')
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('vector_embeddings')
                        ->whereColumn('vector_embeddings.knowledge_base_id', 'knowledge_base.id');
                })
                ->get();
            
            foreach ($entries as $entry) {
                try {
                    // Generate embedding
                    $embedding = $this->generateEmbedding($entry->content);
                    
                    // Add to vector database
                    DB::connection('libsql')->table('vector_embeddings')->insert([
                        'knowledge_base_id' => $entry->id,
                        'title' => $entry->title,
                        'content' => $entry->content,
                        'source_type' => $entry->source_type,
                        'source_id' => $entry->source_id,
                        'keywords' => $entry->keywords,
                        'embedding' => json_encode($embedding),
                        'created_at' => $entry->created_at,
                        'updated_at' => $entry->updated_at
                    ]);
                    
                    $synced++;
                    
                } catch (\Exception $e) {
                    Log::error('Failed to sync knowledge base entry', [
                        'id' => $entry->id,
                        'title' => $entry->title,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
        } catch (\Exception $e) {
            Log::error('Knowledge base sync failed', [
                'error' => $e->getMessage()
            ]);
        }
        
        return $synced;
    }
}
