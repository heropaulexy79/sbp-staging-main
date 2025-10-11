<x-mail::message>
    # Welcome to {{ config('app.name') }}, {{ $user->name }} 🎉

    We’re excited to have you onboard!
    {{ config('app.name') }} is designed to help your organization learn, grow, and succeed.

    Here are some quick resources to get you started:
    - [View FAQs]({{ url('/#faqs') }})
    - [Book a Walkthrough]({{ url('https://calendar.app.google/HwABTcWhqeqNQAX79') }})

    If you ever need support, we’re just a message away.

    Thanks,<br />
    {{ config('app.name') }}
</x-mail::message>
