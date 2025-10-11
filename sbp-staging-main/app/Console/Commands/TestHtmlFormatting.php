<?php

namespace App\Console\Commands;

use App\Services\HybridRagService;
use Illuminate\Console\Command;

class TestHtmlFormatting extends Command
{
    protected $signature = 'rag:test-html-formatting';
    protected $description = 'Test HTML formatting for generated content';

    public function handle()
    {
        $this->info('Testing HTML formatting...');
        
        try {
            $ragService = app(HybridRagService::class);
            
            // Test with a simple title
            $title = 'Introduction to Machine Learning';
            $courseId = 1; // Assuming course ID 1 exists
            
            $this->info("Generating content for: {$title}");
            
            $content = $ragService->generateLessonContent($title, $courseId, [
                'difficulty' => 'beginner',
                'length' => 'short'
            ]);
            
            $this->info("Generated content:");
            $this->line("----------------------------------------");
            $this->line($content);
            $this->line("----------------------------------------");
            
            // Show line count and spacing analysis
            $lines = explode("\n", $content);
            $emptyLines = array_filter($lines, fn($line) => trim($line) === '');
            $this->info("Content analysis:");
            $this->info("- Total lines: " . count($lines));
            $this->info("- Empty lines: " . count($emptyLines));
            $this->info("- HTML elements: " . substr_count($content, '<'));
            
            // Check if content contains HTML tags
            if (str_contains($content, '<h1>') || str_contains($content, '<h2>') || str_contains($content, '<h3>')) {
                $this->info("✓ Content contains HTML headings");
            } else {
                $this->warn("⚠️  Content does not contain HTML headings");
            }
            
            if (str_contains($content, '<p>')) {
                $this->info("✓ Content contains HTML paragraphs");
            } else {
                $this->warn("⚠️  Content does not contain HTML paragraphs");
            }
            
            if (str_contains($content, '<ul>') || str_contains($content, '<ol>')) {
                $this->info("✓ Content contains HTML lists");
            } else {
                $this->info("ℹ️  Content does not contain HTML lists (this is optional)");
            }
            
            $this->info("\n🎉 HTML formatting test completed!");
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Test failed: " . $e->getMessage());
            return 1;
        }
    }
}
