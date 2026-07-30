<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\TestRaceCondition;
use App\Console\Commands\ResetFlashSale;
use App\Console\Commands\HiddenItemGameCommand;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Mendaftarkan command test:flash-sale secara langsung
Artisan::command('test:flash-sale', function () {
    $this->call(TestRaceCondition::class);
})->purpose('Menguji race condition pada endpoint pembuatan pesanan saat flash sale');

Artisan::command('flash-sale:reset', function () {
    $this->call(ResetFlashSale::class);
})->purpose('Reset condition sebelum flash sale');

Artisan::command('game:hidden-item', function () {
    $this->call(HiddenItemGameCommand::class);
})->purpose('Bermain game di Command Line!');
