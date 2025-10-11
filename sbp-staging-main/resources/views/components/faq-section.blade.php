@php
    $faqs = [
        [
            'q' => 'How do I create an account for my organization?',
            'a' =>
                'Sign up on our registration page, enter your organization details, and invite team members from the admin dashboard.',
        ],
        [
            'q' => 'Do learners need separate accounts?',
            'a' =>
                'No — once invited, learners receive an email with login instructions and are automatically added to your organization’s workspace.',
        ],
        [
            'q' => 'Can I upload my own courses and materials?',
            'a' =>
                'Yes, you can upload videos, documents, and interactive content directly, or link external resources.',
        ],
        [
            'q' => 'Does ' . config('app.name') . ' support course tracking',
            'a' => 'Yes — admins can monitor learner progress in real time.',
        ],
        [
            'q' => 'How is pricing structured?',
            'a' =>
                'Pricing is per user per month. Enterprise plans are available for large organizations — contact us for details.',
        ],
        // [
        //     'q' => 'Do you offer a free trial?',
        //     'a' => 'Yes, we provide a 14-day trial with full access to all features.',
        // ],
        [
            'q' => 'What support options are available?',
            'a' => 'You’ll have access to our email support. Enterprise plans include dedicated account managers.',
        ],
        [
            'q' => 'Can we book a live training session for our team?',
            'a' => 'Yes — use our “Book a Walkthrough” page to schedule a guided training with a product specialist.',
        ],
        // [
        //     'q' => 'Is my data secure?',
        //     'a' => 'Yes — we use enterprise-grade encryption, regular security audits, and comply with GDPR standards.',
        // ],
        // [
        //     'q' => 'Where is data stored?',
        //     'a' => 'All data is hosted in secure, globally distributed data centers with redundancy and backups.',
        // ],
    ];
@endphp


<section class="py-32" id="faqs">
    <div class="container max-w-3xl">
        <x-faq :items="$faqs" title=" Frequently asked questions" class="mt-8" />
    </div>
</section>
