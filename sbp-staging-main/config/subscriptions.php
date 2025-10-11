<?php


return [
    'plans' => [
        'starter' => [
            'id' => 'starter',
            'name' => 'Starter',
            'price' => 1000000, // Price in the smallest currency unit (e.g., kobo)
            'currency' => "NGN",
            'description' => "The perfect plan if you're just getting started with our product.",
            "features" => [
                "Unlimited Courses: Create and manage as many courses as your team needs.",
                "Unlimited Members: Add as many users as you like—pay only for active users.",
                "Quizzes & Leaderboard: Enhance engagement with gamified learning tools.",
                "Support: Enjoy assistance tailored to your needs."
            ]
        ],
        'enterprise' => [
            'id' => 'enterprise',
            'name' => 'Enterprise',
            'price' => null, // Custom pricing
            'currency' => "NGN",
            'description' => 'The ideal plan for teams with 500+ members.',
            "features" => [
                "Unlimited Courses: Create and manage as many courses as your team needs.",
                "Unlimited Members: Add as many users as you like—pay only for active users.",
                "Quizzes & Leaderboard: Enhance engagement with gamified learning tools.",
                "Dedicated Support: Enjoy priority assistance tailored to your needs."
            ]
        ],
    ],
];
