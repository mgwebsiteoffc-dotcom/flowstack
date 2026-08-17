<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes & Scheduled Tasks
|--------------------------------------------------------------------------
|
| Scheduled jobs are registered in bootstrap/app.php -> withSchedule()
| (Laravel 11+) and mirrored in app/Console/Kernel.php for Laravel 10
| compatibility. This file intentionally does NOT re-register them to avoid
| double execution.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
