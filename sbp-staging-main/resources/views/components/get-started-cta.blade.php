<section class="bg-primary py-20 text-primary-foreground">
    <div class="mx-auto max-w-2xl p-8 text-center">
        <h2 class="text-4xl font-bold">Get started with {{ config('app.name', 'Laravel') }}
            today
        </h2>
        <p class="mt-2 text-lg">Where culture sustains thriving businesses, and great values shape future leaders.</p>
        <form action="/signup"
            class="mx-auto mt-4 flex max-w-md gap-2 rounded-md bg-background px-3 py-2 ring-offset-background focus-within:outline-none focus-within:ring-2 focus-within:ring-ring focus-within:ring-offset-2">
            <div class="flex-1">
                <input type="email" name='email'
                    class="flex h-10 w-full rounded-md border-0 bg-background px-0 py-2 text-sm outline-none file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-transparent disabled:cursor-not-allowed disabled:opacity-50"
                    placeholder="Enter your work email" />
            </div>
            <button
                class="inline-flex h-10 items-center justify-center gap-2 whitespace-nowrap rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50">
                Get Started
            </button>
        </form>
    </div>

</section>
