<?php

namespace App\Http\Controllers;

use App\Services\HybridRagService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RagController extends Controller
{
    public function __construct(
        private HybridRagService $ragService
    ) {}
    
    public function generateContent(Request $request)
    {
        // Log the incoming request for debugging
        \Log::info('RAG Generate Content Request', [
            'request_data' => $request->all(),
            'course_id_type' => gettype($request->course_id),
            'course_id_value' => $request->course_id
        ]);
        
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'course_id' => 'required|exists:courses,id',
            'options' => 'array'
        ]);
        
        if ($validator->fails()) {
            \Log::error('RAG Validation Failed', [
                'errors' => $validator->errors(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $content = $this->ragService->generateLessonContent(
                $request->title,
                $request->course_id,
                $request->options ?? []
            );
            
            return response()->json([
                'success' => true,
                'content' => $content
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate content: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function addKnowledge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'source_type' => 'required|in:course,lesson,external',
            'source_id' => 'nullable|integer',
            'keywords' => 'nullable|string'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $success = $this->ragService->addKnowledgeBaseEntry(
            $request->title,
            $request->content,
            $request->source_type,
            $request->source_id,
            $request->keywords
        );
        
        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Knowledge base entry added successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add knowledge base entry'
            ], 500);
        }
    }
    
    public function searchKnowledge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:20'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $results = $this->ragService->searchKnowledgeBase(
                $request->query,
                $request->limit ?? 5
            );
            
            return response()->json([
                'success' => true,
                'results' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function logLessonGeneration(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lesson_id' => 'required|exists:lessons,id',
            'prompt' => 'required|string',
            'generated_content' => 'required|string',
            'model_used' => 'nullable|string|max:100'
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            DB::table('ai_generations')->insert([
                'lesson_id' => $request->lesson_id,
                'prompt' => $request->prompt,
                'generated_content' => $request->generated_content,
                'model_used' => $request->model_used ?? 'gemini-1.5-flash',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Generation logged successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to log generation: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function testConnection()
    {
        try {
            // Check if Gemini API key is configured
            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'GEMINI_API_KEY is not configured'
                ], 500);
            }
            
            // Test database connections
            $knowledgeCount = DB::table('knowledge_base')->count();
            $vectorCount = DB::connection('libsql')->table('vector_embeddings')->count();
            
            return response()->json([
                'success' => true,
                'message' => 'Hybrid RAG system is ready',
                'data' => [
                    'gemini_configured' => true,
                    'mysql_knowledge_entries' => $knowledgeCount,
                    'libsql_vector_entries' => $vectorCount,
                    'sync_status' => $knowledgeCount === $vectorCount ? 'synced' : 'needs_sync'
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'RAG system test failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function syncKnowledgeBase()
    {
        try {
            $synced = $this->ragService->syncKnowledgeBaseToVectors();
            
            return response()->json([
                'success' => true,
                'message' => "Successfully synced {$synced} knowledge base entries to vector database"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage()
            ], 500);
        }
    }
}
