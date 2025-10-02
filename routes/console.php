<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule game timer processing every second
Schedule::command('timers:process')->everySecond();

// Calculate recommendations every 6 hours
Schedule::command('app:calculate-recommendations')->everySixHours();
