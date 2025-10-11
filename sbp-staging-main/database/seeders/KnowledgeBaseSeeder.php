<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KnowledgeBaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $knowledgeEntries = [
            [
                'title' => 'Introduction to Learning Management Systems',
                'content' => 'A Learning Management System (LMS) is a software application that provides the framework that handles all aspects of the learning process. It is used for delivering, tracking, and managing training and educational content. Key features include course creation, student enrollment, progress tracking, and assessment tools.',
                'source_type' => 'external',
                'source_id' => null,
                'keywords' => 'LMS, learning management, education, training, e-learning, course management',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Best Practices for Online Course Design',
                'content' => 'Effective online course design follows several key principles: 1) Clear learning objectives that are measurable and achievable, 2) Engaging multimedia content that caters to different learning styles, 3) Interactive elements like quizzes and discussions, 4) Regular assessments to track progress, 5) Mobile-responsive design for accessibility, 6) Consistent navigation and structure throughout the course.',
                'source_type' => 'external',
                'source_id' => null,
                'keywords' => 'course design, online learning, e-learning, instructional design, best practices, engagement',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Student Engagement Strategies',
                'content' => 'Keeping students engaged in online courses requires active strategies: Use interactive content like videos, simulations, and gamification elements. Implement discussion forums and peer collaboration tools. Provide regular feedback and personalized learning paths. Create bite-sized lessons with clear progress indicators. Use real-world examples and case studies to make content relevant.',
                'source_type' => 'external',
                'source_id' => null,
                'keywords' => 'student engagement, online learning, interaction, motivation, participation, learning experience',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'title' => 'Assessment and Evaluation Methods',
                'content' => 'Effective assessment in online learning includes multiple evaluation methods: Formative assessments (quizzes, discussions, peer reviews) for ongoing feedback, Summative assessments (final exams, projects, portfolios) for comprehensive evaluation, Self-assessments and reflection activities, Peer assessments to encourage collaboration, Automated grading for objective questions, Rubric-based evaluation for subjective content.',
                'source_type' => 'external',
                'source_id' => null,
                'keywords' => 'assessment, evaluation, testing, grading, feedback, learning outcomes, measurement',
                'created_at' => now(),
                'updated_at' => now()
            ]
        ];

        DB::table('knowledge_base')->insert($knowledgeEntries);
    }
}


