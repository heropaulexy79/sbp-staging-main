<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
@php
    $tag_id = config('goole-analytics.tag_id');
@endphp

<head>
    @if (!empty($tag_id))
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $tag_id }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ $tag_id }}'); // Quotes are important here!
    </script>
    @endif

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />


    {{-- <link rel="icon" type="image/x-icon" href="/favicon.ico" /> --}}
    <link rel="icon" type="image/x-icon" href="/images/favicon.ico" />


    @vite('resources/css/app.css')
</head>

<body class="font-sans antialiased">

    <img id="background" class="pointer-events-none absolute inset-0 h-screen w-full bg-right object-cover"
        src="/images/home-bg.webp" />

    <div class="relative min-h-screen">
        <x-layout.header />
        <main class="mt-6">

            <section>
                <div class="container">
                    <div class="max-w-2xl px-8 py-20 pb-32">
                        <h1 class="text-balance text-4xl font-bold md:text-5xl lg:text-6xl">From onboarding to mastery.
                        </h1>
                        <p class="mt-2 text-lg"> The right values aren't just learnt; they're built.
                            <br />
                            Culture is the key to progress and productivity, whether in the classroom,
                            boardroom or society.
                        </p>
                        <div class="mt-5 inline-flex gap-4">
                            <a href="{{ route('register') }}"
                                class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                                Get Started
                            </a>

                            <a href="{{ route('login') }}"
                                class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md border border-primary bg-transparent px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                                Log in
                            </a>
                        </div>

                    </div>
                </div>
            </section>


            <x-features />

            <x-pricing />

            <x-faq-section />


            <x-get-started-cta />

        </main>
        <x-layout.footer />

    </div>
</body>
<script defer src="//unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>


</html>
