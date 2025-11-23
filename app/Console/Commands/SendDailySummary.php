<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendDailySummary extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-daily-summary';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
 public function handle()
{
    // Your daily summary logic

    $today = now()->toDateString();

    $totalSales = \App\Models\Sale::whereDate('created_at', $today)->sum('total');
    $totalVat   = \App\Models\Sale::whereDate('created_at', $today)->sum('vat_amount');
    $count      = \App\Models\Sale::whereDate('created_at', $today)->count();

    $adminEmail = \App\Models\Setting::get('notify_admin_email');

    if ($adminEmail) {
        \Mail::to($adminEmail)->send(
            new \App\Mail\DailySummaryMail($totalSales, $totalVat, $count, $today)
        );
    }

    $this->info('Daily summary email sent.');
}


}
