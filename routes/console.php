<?php

use App\Console\Commands\RestoreItemStock;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduler: ejecutar cada minuto la reposición de stock
Schedule::command('booking:restore-stock')->everyMinute();
// Scheduler: ejecutar cada hora la limpieza de archivos temporales 'antiguos'
Schedule::command('booking:clear-tmp')->hourly();