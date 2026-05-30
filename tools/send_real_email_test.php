<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Mail;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

Mail::raw(
    'SolidCare SSD real email test sent at ' . now()->toDateTimeString(),
    function ($message): void {
        $message
            ->to(config('mail.from.address'))
            ->subject('SolidCare SSD email test');
    }
);

echo "sent\n";
