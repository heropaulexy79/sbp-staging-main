@props([
    'items' => [],
    'title' => 'FAQs',
])

<div {{ $attributes->merge(['class' => 'mx-auto max-w-3xl']) }} x-data="{ open: null }">
    <h2 class="mb-4 text-center text-3xl font-semibold text-foreground md:mb-11 md:text-4xl">{{ $title }}</h2>

    <div class="space-y-2">
        @foreach ($items as $i => $item)
            @php
                $qid = 'faq-q-' . $i;
                $aid = 'faq-a-' . $i;
            @endphp

            <div class="rounded-2xl border bg-card text-card-foreground shadow-sm">
                <button type="button" class="flex w-full items-center justify-between gap-3 px-4 py-3"
                    :aria-expanded="open === {{ $i }} ? 'true' : 'false'" aria-controls="{{ $aid }}"
                    id="{{ $qid }}"
                    @click="open === {{ $i }} ? open = null : open = {{ $i }}">
                    <span class="text-left font-medium">{{ $item['q'] }}</span>

                    <svg class="h-4 w-4 transition-transform duration-200"
                        :class="open === {{ $i }} ? 'rotate-180' : ''" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        aria-hidden="true">
                        <path d="M6 9l6 6 6-6" />
                    </svg>
                </button>

                <div id="{{ $aid }}" role="region" aria-labelledby="{{ $qid }}"
                    x-show="open === {{ $i }}" x-collapse class="px-4 pb-4 text-sm text-muted-foreground">
                    {!! nl2br(e($item['a'])) !!}
                </div>
            </div>
        @endforeach
    </div>
</div>
