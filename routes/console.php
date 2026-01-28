<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


/**
 * Scheduler (Laravel 11 style)
 * External cron should hit GET /cron/run every minute.
 */
Schedule::command('sequence:tick')
  ->everyMinute()
  ->withoutOverlapping(10);