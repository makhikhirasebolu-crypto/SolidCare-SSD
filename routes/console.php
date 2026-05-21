<?php

use App\Mail\ClinicStockExpiryReminder;
use App\Models\ClinicStockReceipt;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('clinic:send-expiry-reminders', function () {
    $recipients = User::query()
        ->whereIn('role', ['senior_nurse_officer', 'executive'])
        ->whereNotNull('email')
        ->pluck('email')
        ->filter(fn ($email) => is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL))
        ->unique()
        ->values();

    if ($recipients->isEmpty()) {
        $this->warn('No senior nurse officer or executive email addresses found.');

        return 0;
    }

    $notices = [
        [
            'date' => now()->addMonthNoOverflow()->toDateString(),
            'sent_column' => 'expiry_month_notice_sent_at',
            'window' => '1 month',
        ],
        [
            'date' => now()->addWeek()->toDateString(),
            'sent_column' => 'expiry_week_notice_sent_at',
            'window' => '1 week',
        ],
    ];

    $sentCount = 0;

    foreach ($notices as $notice) {
        ClinicStockReceipt::query()
            ->with('stockItem')
            ->whereDate('expiry_date', $notice['date'])
            ->whereNull($notice['sent_column'])
            ->chunkById(100, function ($receipts) use ($recipients, $notice, &$sentCount) {
                foreach ($receipts as $receipt) {
                    if (! $receipt->stockItem || $receipt->stockItem->balance <= 0) {
                        continue;
                    }

                    foreach ($recipients as $email) {
                        Mail::to($email)->send(new ClinicStockExpiryReminder($receipt, $notice['window']));
                    }

                    $receipt->forceFill([
                        $notice['sent_column'] => now(),
                    ])->save();

                    $sentCount += $recipients->count();
                }
            });
    }

    $this->info($sentCount . ' clinic stock expiry reminder email(s) sent.');

    return 0;
})->purpose('Send clinic stock expiry reminders to senior nurse officers and executives.');

Schedule::command('clinic:send-expiry-reminders')->daily();
