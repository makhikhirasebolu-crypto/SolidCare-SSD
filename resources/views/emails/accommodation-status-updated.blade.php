<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $heading }}</title>
    </head>
    <body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
        <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
            <div style="padding:24px 28px; background:#0f172a; color:#f8fafc;">
                <h1 style="margin:0; font-size:24px;">{{ $heading }}</h1>
            </div>

            <div style="padding:28px;">
                <p style="margin-top:0;">Hello {{ $application->full_name }},</p>

                <p>{{ $summary }}</p>

                <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; margin:20px 0;">
                    <p style="margin:0 0 10px;"><strong>Status:</strong> {{ $statusLabel }}</p>
                    @if ($application->status === 'admitted' && $application->room)
                        <p style="margin:0 0 10px;"><strong>Allocated Room:</strong> {{ $application->room->block_name }}-{{ str_pad((string) $application->room->room_number, 2, '0', STR_PAD_LEFT) }}</p>
                    @endif
                    @if ($application->status === 'admitted' && $application->check_in_date)
                        <p style="margin:0 0 10px;"><strong>Check-In Date:</strong> {{ $application->check_in_date->format('F j, Y') }}</p>
                    @endif
                    @if ($application->status === 'rejected' && $application->rejection_reason)
                        <p style="margin:0 0 10px;"><strong>Reason:</strong> {{ $application->rejection_reason }}</p>
                    @endif
                    <p style="margin:0;"><strong>Updated:</strong> {{ optional($application->updated_at)->format('F j, Y g:i A') ?? now()->format('F j, Y g:i A') }}</p>
                </div>

                <p>{{ $nextSteps }}</p>

                <p style="margin-bottom:0;">Thank you,<br>SolidCare SSD</p>
            </div>
        </div>
    </body>
</html>
