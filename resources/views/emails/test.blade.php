<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $subjectText ?? 'SolidCare SSD Test Email' }}</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <h1 style="font-size: 22px; margin-bottom: 16px;">SolidCare SSD</h1>
    <p>{!! nl2br(e($bodyText)) !!}</p>
</body>
</html>
