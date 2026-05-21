<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Clinic Stock Expiry Reminder</title>
</head>
<body style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5;">
    <h2>Clinic stock expiry reminder</h2>

    <p>
        {{ optional($receipt->stockItem)->medicine_name ?? 'A clinic stock item' }}
        is due to expire in {{ $noticeWindow }}.
    </p>

    <ul>
        <li><strong>Medicine:</strong> {{ optional($receipt->stockItem)->medicine_name ?? 'Unknown' }}</li>
        <li><strong>Quantity recorded:</strong> {{ number_format($receipt->quantity_received) }}</li>
        <li><strong>Current available balance:</strong> {{ number_format(optional($receipt->stockItem)->balance ?? 0) }}</li>
        <li><strong>Date recorded:</strong> {{ optional($receipt->received_date)->format('F j, Y') ?? 'Not available' }}</li>
        <li><strong>Expiry date:</strong> {{ optional($receipt->expiry_date)->format('F j, Y') ?? 'Not available' }}</li>
    </ul>

    <p>Please review the item while stock is still available.</p>
</body>
</html>
