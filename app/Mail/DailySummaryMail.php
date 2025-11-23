<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class DailySummaryMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $summary;

    public function __construct(array $summary)
    {
        $this->summary = $summary;
    }

    public function build()
    {
        return $this->subject('Daily Sales Summary')
                    ->view('emails.daily-summary');
    }
}
