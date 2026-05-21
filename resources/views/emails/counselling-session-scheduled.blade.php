<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Your counselling session has been scheduled</title>
    </head>
    <body style="margin:0; padding:24px; background:#f8fafc; font-family:Arial, Helvetica, sans-serif; color:#1f2937;">
        <div style="max-width:640px; margin:0 auto; background:#ffffff; border:1px solid #e5e7eb; border-radius:16px; overflow:hidden;">
            <div style="padding:24px 28px; background:#0f172a; color:#f8fafc;">
                <h1 style="margin:0; font-size:24px;">Counselling Session Scheduled</h1>
            </div>

            <div style="padding:28px;">
                <p style="margin-top:0;">Hello {{ $booking->student_name ?: optional($booking->user)->name ?: 'Student' }},</p>

                <p>Your counselling session has been scheduled by the Counselling Management Desk.</p>

                <div style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; margin:20px 0;">
                    <p style="margin:0 0 10px;"><strong>Session Date:</strong> {{ $booking->appointment_date?->format('F j, Y') ?? '-' }}</p>
                    <p style="margin:0 0 10px;"><strong>Session Time:</strong> {{ $booking->appointment_date?->format('g:i A') ?? '-' }}</p>
                    <p style="margin:0 0 10px;"><strong>Status:</strong> Scheduled</p>
                    @if ($booking->counsellor_notes)
                        <p style="margin:0;"><strong>Notes:</strong> {{ $booking->counsellor_notes }}</p>
                    @endif
                </div>

                <p>Please log in to SolidCare SSD to review the counselling booking details.</p>

                <p style="margin-bottom:0;">Thank you,<br>SolidCare SSD</p>
            </div>
        </div>
    </body>
</html>
