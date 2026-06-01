<?php

use App\Mail\ClinicStockExpiryReminder;
use App\Models\ClinicStockReceipt;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('mail:brevo-test {email : The real email address that should receive the test message}', function (string $email) {
    if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $this->error('Please provide a valid email address.');

        return 1;
    }

    $this->info('Mailer: ' . config('mail.default'));
    $this->info('Host: ' . config('mail.mailers.smtp.host'));
    $this->info('Port: ' . config('mail.mailers.smtp.port'));
    $this->info('Queue: ' . config('queue.default'));
    $this->info('Sending Brevo SMTP test email to ' . $email . '...');

    try {
        Mail::raw('This is a test email from SolidCare SSD through Brevo SMTP.', function ($message) use ($email) {
            $message->to($email)
                ->subject('SolidCare SSD Brevo Test');
        });
    } catch (Throwable $e) {
        Log::error('Brevo SMTP test email failed.', [
            'recipient' => $email,
            'error' => $e->getMessage(),
        ]);

        $this->error('Email could not be sent: ' . $e->getMessage());
        $this->warn('Check storage/logs/laravel.log for the full mail error.');

        return 1;
    }

    $this->info('Test email handed off successfully. Check the inbox and spam folder.');

    return 0;
})->purpose('Send a direct Brevo SMTP test email.');

Artisan::command('students:verification-links {--email= : Show only this student email}', function () {
    $query = User::query()
        ->where('role', 'student')
        ->whereNull('email_verified_at')
        ->whereNotNull('email');

    if ($email = $this->option('email')) {
        $query->whereRaw('LOWER(TRIM(email)) = ?', [Str::lower(trim((string) $email))]);
    }

    $students = $query
        ->orderBy('id')
        ->get(['id', 'name', 'email', 'email_verified_at', 'role']);

    if ($students->isEmpty()) {
        $this->info('No unverified student accounts matched.');

        return 0;
    }

    foreach ($students as $student) {
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            [
                'id' => $student->id,
                'hash' => sha1($student->getEmailForVerification()),
            ],
            false
        );

        $this->line($student->id . ' | ' . $student->name . ' | ' . $student->email);
        $this->line($verificationUrl);
        $this->newLine();
    }

    $this->info($students->count() . ' verification link(s) generated. Links expire in 60 minutes.');

    return 0;
})->purpose('Show verification links for existing unverified student accounts.');

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
