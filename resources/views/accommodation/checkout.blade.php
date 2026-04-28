<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>SSD Checkout Request</title>

<style>

/* RESET */
* {
    box-sizing: border-box;
}

/* BODY */
body {
    margin: 0;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background: linear-gradient(135deg, #667eea, #764ba2);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 20px;
}

/* FORM CONTAINER */
.form-container {
    background: #ffffff;
    padding: 35px 30px;
    border-radius: 12px;
    width: 100%;
    max-width: 700px;
    box-shadow: 0 12px 30px rgba(0,0,0,0.2);
    animation: fadeIn 0.6s ease-in-out;
}

/* FADE ANIMATION */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* HEADINGS */
h2 {
    text-align: center;
    color: #222;
    margin-bottom: 5px;
    font-size: 24px;
    font-weight: 700;
}

h3 {
    text-align: center;
    color: #666;
    margin-bottom: 25px;
    font-size: 16px;
    font-weight: 500;
}

/* LABELS */
label {
    display: block;
    margin-top: 15px;
    font-weight: 600;
    color: #444;
    font-size: 14px;
}

/* INPUTS */
input, select, textarea {
    width: 100%;
    padding: 11px;
    margin-top: 5px;
    border-radius: 6px;
    border: 1px solid #ccc;
    font-size: 14px;
    transition: all 0.25s ease;
    background: #fafafa;
}

/* INPUT FOCUS */
input:focus, select:focus, textarea:focus {
    border-color: #667eea;
    background: #fff;
    box-shadow: 0 0 6px rgba(102,126,234,0.4);
    outline: none;
}

/* TEXTAREA */
textarea {
    resize: vertical;
}

/* BUTTON */
button {
    margin-top: 25px;
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #667eea, #5a67d8);
    color: #fff;
    font-size: 16px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

/* BUTTON HOVER */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* BUTTON CLICK */
button:active {
    transform: scale(0.98);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-container {
        padding: 25px 20px;
    }

    h2 { font-size: 20px; }
    h3 { font-size: 14px; }
}

</style>
</head>

<body>

<div class="form-container">

<h2>SSD003 STUDENT SERVICE DEPARTMENT</h2>
<h3>STUDENT ACCOMMODATION CHECKOUT REQUEST</h3>

@if (session('success'))
    <div style="margin-bottom: 15px; color: #0f5132; background: #d1e7dd; padding: 12px; border-radius: 8px; border: 1px solid #badbcc;">
        {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div style="margin-bottom: 15px; color: #842029; background: #f8d7da; padding: 12px; border-radius: 8px; border: 1px solid #f5c2c7;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($application->status === 'checkout_rejected' && $application->rejection_reason)
    <div style="margin-bottom: 15px; color: #842029; background: #f8d7da; padding: 12px; border-radius: 8px; border: 1px solid #f5c2c7;">
        <strong>Previous rejection reason:</strong> {{ $application->rejection_reason }}
    </div>
@endif

<form method="POST" action="{{ route('student.accommodation.checkout.store') }}">
@csrf

<label>Full Names</label>
<input type="text" name="full_name" value="{{ old('full_name', $user->name) }}" required>

<label>Student ID</label>
<input type="text" name="student_id" value="{{ old('student_id', $user->student_id ?? $user->id_number ?? '') }}" required>

<label>Checkout Date</label>
<input type="date" name="checkout_date" value="{{ old('checkout_date') }}" required>

<label>Reason for Checkout</label>
<textarea name="reason" rows="4" required>{{ old('reason') }}</textarea>

<button type="submit">Submit Checkout Request</button>

</form>

</div>

</body>
</html>
