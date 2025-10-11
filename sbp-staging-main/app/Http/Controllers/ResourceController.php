<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Resource;
use App\Services\HybridRagService;
use Smalot\PdfParser\Parser as PdfParser;
use PhpOffice\PhpWord\IOFactory;

class ResourceController extends Controller
{
    private HybridRagService $ragService;

    public function __construct(HybridRagService $ragService)
    {
        $this->ragService = $ragService;
    }

    /**
     * Upload and process a document or text
     */
    public function uploadResource(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'file' => 'nullable|file|mimes:pdf,docx,txt|max:10240', // 10MB max
            'text' => 'nullable|string|max:50000',
            'title' => 'required|string|max:255',
            'course_id' => 'nullable|integer|exists:courses,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $title = $request->input('title');
            $courseId = $request->input('course_id');
            $text = '';

            // Process file upload
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $text = $this->extractTextFromFile($file);
            } 
            // Process raw text
            elseif ($request->has('text')) {
                $text = $request->input('text');
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Either file or text must be provided'
                ], 400);
            }

            if (empty(trim($text))) {
                return response()->json([
                    'success' => false,
                    'message' => 'No text content found to process'
                ], 400);
            }

            // Normalize text
            $text = $this->normalizeText($text);

            // Store the resource with chunking and embeddings
            $result = $this->storeResource($text, $title, $courseId, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Resource processed successfully',
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Resource upload failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process resource: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Extract text from uploaded file
     */
    private function extractTextFromFile($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $content = '';

        switch ($extension) {
            case 'pdf':
                $content = $this->extractTextFromPdf($file);
                break;
            case 'docx':
                $content = $this->extractTextFromDocx($file);
                break;
            case 'txt':
                $content = file_get_contents($file->getPathname());
                // Clean the text content immediately
                $content = $this->normalizeText($content);
                break;
            default:
                throw new \Exception("Unsupported file type: {$extension}");
        }

        return $content;
    }

    /**
     * Extract text from PDF file
     */
    private function extractTextFromPdf($file): string
    {
        try {
            $parser = new PdfParser();
            $pdf = $parser->parseFile($file->getPathname());
            $text = $pdf->getText();
            
            // Clean the extracted text immediately
            $text = $this->normalizeText($text);
            
            return $text;
        } catch (\Exception $e) {
            throw new \Exception("Failed to extract text from PDF: " . $e->getMessage());
        }
    }

    /**
     * Extract text from DOCX file
     */
    private function extractTextFromDocx($file): string
    {
        try {
            $phpWord = IOFactory::load($file->getPathname());
            $text = '';

            foreach ($phpWord->getSections() as $section) {
                foreach ($section->getElements() as $element) {
                    if (method_exists($element, 'getText')) {
                        $text .= $element->getText() . "\n";
                    }
                }
            }

            // Clean the extracted text immediately
            $text = $this->normalizeText($text);
            
            return $text;
        } catch (\Exception $e) {
            throw new \Exception("Failed to extract text from DOCX: " . $e->getMessage());
        }
    }

    /**
     * Normalize text content
     */
    private function normalizeText(string $text): string
    {
        // Handle encoding issues more robustly
        if (!mb_check_encoding($text, 'UTF-8')) {
            // Try to detect and convert encoding
            $detectedEncoding = mb_detect_encoding($text, ['UTF-8', 'ISO-8859-1', 'Windows-1252', 'ASCII'], true);
            if ($detectedEncoding) {
                $text = mb_convert_encoding($text, 'UTF-8', $detectedEncoding);
            } else {
                // Fallback: remove invalid sequences
                $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            }
        }
        
        // Remove null bytes and other control characters
        $text = str_replace(["\0", "\x00"], '', $text);
        
        // Remove BOM if present
        $text = str_replace(["\xEF\xBB\xBF", "\xFE\xFF", "\xFF\xFE"], '', $text);
        
        // Remove extra whitespace and normalize line breaks
        $text = preg_replace('/\s+/', ' ', $text);
        $text = preg_replace('/\n\s*\n/', "\n\n", $text);
        
        // Remove special characters that might interfere with processing
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Remove any remaining non-printable characters except newlines and tabs
        $text = preg_replace('/[^\x20-\x7E\x0A\x0D\x09]/', '', $text);
        
        // Clean up any double spaces that might have been created
        $text = preg_replace('/\s{2,}/', ' ', $text);
        
        // Final UTF-8 validation
        if (!mb_check_encoding($text, 'UTF-8')) {
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        }
        
        return trim($text);
    }

    /**
     * Store resource as a single record with embedding
     */
    private function storeResource(string $text, string $title, ?int $courseId, $file = null): array
    {
        try {
            // Validate text content
            if (empty(trim($text))) {
                throw new \Exception('Text content is empty after processing');
            }

            // Ensure text is valid UTF-8 and clean
            $text = $this->normalizeText($text);
            
            if (empty(trim($text))) {
                throw new \Exception('Text content is empty after normalization');
            }

            // Test if the text can be JSON encoded
            $testJson = json_encode($text);
            if ($testJson === false) {
                throw new \Exception('Text contains invalid characters that cannot be JSON encoded');
            }

            // Generate embedding for the entire document
            $embedding = $this->ragService->generateEmbedding($text);
            
            // Prepare metadata
            $metadata = [
                'content_size' => strlen($text),
                'source_type' => $file ? $file->getClientOriginalExtension() : 'text',
                'original_filename' => $file ? $file->getClientOriginalName() : null,
                'uploaded_at' => now()->toISOString(),
            ];

            // Test if metadata can be JSON encoded
            $testMetadataJson = json_encode($metadata);
            if ($testMetadataJson === false) {
                throw new \Exception('Metadata contains invalid characters that cannot be JSON encoded');
            }

            // Create single resource record
            $resource = Resource::create([
                'course_id' => $courseId,
                'title' => $title,
                'content' => $text,
                'embedding' => $embedding,
                'metadata' => $metadata
            ]);

            Log::info('Resource stored successfully', [
                'resource_id' => $resource->id,
                'title' => $title,
                'content_length' => strlen($text)
            ]);

            return [
                'total_chunks' => 1,
                'resources' => [
                    [
                        'id' => $resource->id,
                        'title' => $resource->title,
                        'content_length' => strlen($text)
                    ]
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Failed to store resource', [
                'title' => $title,
                'content_length' => strlen($text ?? ''),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }


    /**
     * Get resources for a course
     */
    public function getCourseResources(Request $request, int $courseId): JsonResponse
    {
        try {
            $resources = Resource::forCourse($courseId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $resources
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get course resources', [
                'course_id' => $courseId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve resources'
            ], 500);
        }
    }

    /**
     * Search resources using vector similarity
     */
    public function searchResources(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|max:1000',
            'course_id' => 'nullable|integer|exists:courses,id',
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
            $query = $request->input('query');
            $courseId = $request->input('course_id');
            $limit = $request->input('limit', 5);

            $results = $this->ragService->searchResources($query, $courseId, $limit);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);

        } catch (\Exception $e) {
            Log::error('Resource search failed', [
                'query' => $request->input('query'),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Search failed'
            ], 500);
        }
    }
}
