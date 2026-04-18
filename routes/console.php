<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Tarea para actualizar estado de comprobantes en SUNAT a las 10am todos los días
Schedule::command('sunat:actualizar-estado-comprobantes')->dailyAt('10:00');
