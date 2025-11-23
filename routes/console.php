<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\DailySummaryMail;
use App\Models\Sale;

// Example command (default)
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// ✅ New command: send:daily-summary
Artisan::command('send:daily-summary', function () {
    $this->info('Sending daily summary email...');

    // Example: summary for today
    $today = now()->toDateString();

    $salesQuery = Sale::whereDate('created_at', $today)->where('status', '!=', 'paused');
    $totalSales = $salesQuery->sum('total');
    $countSales = $salesQuery->count();

    $summary = [
        'date'        => $today,
        'totalSales'  => $totalSales,
        'countSales'  => $countSales,
    ];

    // CHANGE THIS to your real admin email or use Setting::get(...)
    $to = 'admin@example.com';

    Mail::to($to)->send(new DailySummaryMail($summary));

    $this->info('Daily summary email sent.');
})->purpose('Send the daily sales summary email');

// ✅ Schedule it (for now: every minute so you can test)
Schedule::command('send:daily-summary')->dailyAt('23:59');


// Later, when you’re happy:
// Schedule::command('send:daily-summary')->dailyAt('23:59');
