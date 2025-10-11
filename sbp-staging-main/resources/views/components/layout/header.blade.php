<header class="">
    <div class="container grid grid-cols-2 items-center gap-2 border-b border-black/5 py-4 lg:grid-cols-2">
        <div>
            <a href="/">
                <img src="{{ asset('images/logo_001-primary.svg') }}" alt="logo" class="max-h-12" />
            </a>
        </div>

        @if (Route::has('login'))
            <nav class="flex flex-1 justify-end gap-4">
                @auth
                    <a href="{{ url('/dashboard') }}"
                        class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md border border-primary bg-transparent px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                        Log in
                    </a>

                    @if (Route::has('register'))
                        <a href="{{ route('register') }}"
                            class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                            Get started
                        </a>
                    @endif
                @endauth
            </nav>
        @endif
    </div>
</header>
