<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LowStockAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $products;
    public $threshold;

    public function __construct($products, $threshold)
    {
        $this->products  = $products;
        $this->threshold = $threshold;
    }

    public function build()
    {
        return $this->subject('Low Stock Alert')
                    ->view('emails.low-stock-alert');
    }
}

