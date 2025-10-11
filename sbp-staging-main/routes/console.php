<?php

use App\Jobs\ProcessSubscriptionBilling;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// Artisan::command('inspire', function () {
//     $this->comment(Inspiring::quote());
// })->purpose('Display an inspiring quote')->hourly();


// Schedule::command(BillOrganisationsMonthly::class)->monthly()
//     ->environments(['production'])
//     ->runInBackground();

Schedule::job(new ProcessSubscriptionBilling)
    ->dailyAt('1:00')
    ->withoutOverlapping()
    ->environments(['staging', 'production']);


// Schedule::command('queue:work --stop-when-empty')
//     ->everyMinute()
//     ->withoutOverlapping();
