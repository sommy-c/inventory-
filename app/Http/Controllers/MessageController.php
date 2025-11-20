<?php

namespace App\Http\Controllers;

use Twilio\Rest\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;


class MessageController extends Controller
{
    

    public function sendEmail(Request $request)
    {
        $data = $request->validate([
            'to'      => 'required|string',
            'cc'      => 'nullable|string',
            'bcc'     => 'nullable|string',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        // Convert comma-separated list to array
        $to  = $this->parseEmails($data['to']);
        $cc  = $this->parseEmails($data['cc'] ?? '');
        $bcc = $this->parseEmails($data['bcc'] ?? '');

        if (empty($to)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid recipient emails found.',
            ], 422);
        }

        // Use a simple raw email for now
        foreach ($to as $recipient) {
            Mail::raw($data['message'], function ($message) use ($recipient, $data, $cc, $bcc) {
                $message->to($recipient)
                        ->subject($data['subject']);

                if (!empty($cc)) {
                    $message->cc($cc);
                }

                if (!empty($bcc)) {
                    $message->bcc($bcc);
                }
            });
        }

        return response()->json([
            'success' => true,
            'message' => 'Email sent successfully.',
        ]);
    }

   public function sendSms(Request $request)
{
    $data = $request->validate([
        'phone' => 'required|string',
        'message' => 'required|string'
    ]);

    $phones = explode(',', $data['phone']);

    $apiUrl = env('MTN_SMS_URL');
    $apiKey = env('MTN_SMS_KEY');
    $sender = env('MTN_SMS_SENDER');

    foreach ($phones as $phone) {
        $payload = [
            "sender" => $sender,
            "recipient" => trim($phone),
            "message" => $data['message']
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer $apiKey",
            'Content-Type' => 'application/json',
        ])->post($apiUrl, $payload);

        if (!$response->successful()) {
            \Log::error('MTN SMS FAILED', [
                'phone' => $phone,
                'response' => $response->body()
            ]);
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'SMS sent via MTN API.'
    ]);
}


    // ----------------- Helpers -----------------

    private function parseEmails(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn($email) => trim($email))
            ->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
            ->values()
            ->all();
    }

    private function parsePhones(string $value): array
    {
        return collect(explode(',', $value))
            ->map(fn($phone) => trim($phone))
            ->filter()
            ->values()
            ->all();
    }
}
