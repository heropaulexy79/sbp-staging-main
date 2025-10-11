<footer class="mx-auto mt-auto w-full max-w-[85rem] px-4 py-10 sm:px-6 lg:px-8">

    <div class="mb-10 flex justify-between gap-6">
        <div class="hidden max-w-[500px] sm:block">
            {{-- <a class="flex-none text-xl font-semibold focus:opacity-80 focus:outline-none" href="#"
                aria-label="Brand">Brand</a> --}}

            <a href="/">
                <img src="{{ asset('images/logo_001-primary.svg') }}" alt="logo" class="max-h-12" />
            </a>

            <p class="mt-3 text-xs text-muted-foreground sm:text-sm">
                © {{ date('Y') }} Neukleos Studios.
            </p>
        </div>
        <div class="mb-10 grid grid-cols-2 gap-20 md:grid-cols-3 lg:grid-cols-3 lg:gap-[200px]">

            <div>
                <h4 class="text-xs font-semibold uppercase text-foreground">Product</h4>

                <ul class="mt-3 grid space-y-3 text-sm">
                    <li>
                        <a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href="{{ route('register') }}">Get Started</a>
                    </li>
                    <li>
                        <a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href="/#pricing">Pricing</a>
                    </li>
                    <li>
                        <a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href="/#features">Features</a>
                    </li>
                    <li>
                        <a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href="/#faqs">Frequently Asked Questions</a>
                    </li>
                    <li>
                        <a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href="/#contact">Contact</a>
                    </li>
                    <li>
                        <a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href="https://calendar.app.google/HwABTcWhqeqNQAX79" target="_blank">Book A Walkthrough</a>
                    </li>
                </ul>
            </div>






            <div>
                <h4 class="text-xs font-semibold uppercase text-foreground">Legal</h4>

                <ul class="mt-3 grid space-y-3 text-sm">
                    <li><a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href={{ route('website.terms') }}>Terms</a></li>
                    <li><a class="inline-flex gap-x-2 text-muted-foreground hover:text-foreground focus:text-foreground focus:outline-none"
                            href={{ route('website.privacy') }}>Privacy</a></li>
                </ul>

            </div>

        </div>
    </div>

</footer>
