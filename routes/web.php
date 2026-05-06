<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AcademicReferralController;

Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.store');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/clinic', [AuthController::class, 'clinic'])->name('clinic');
Route::get('/student/clinic', [AuthController::class, 'studentClinic'])->name('student.clinic.index');
Route::post('/student/clinic', [AuthController::class, 'storeStudentClinic'])->name('student.clinic.store');
Route::get('/counselling', [AuthController::class, 'counselling'])->name('counselling');
Route::get('/counselling/report/download', [AuthController::class, 'downloadCounsellingReport'])->name('counselling.report.download');
Route::post('/counselling/emergency/reply', [AuthController::class, 'emergencyCounsellingReply'])->name('counselling.emergency.reply');
Route::post('/counselling/bookings', [AuthController::class, 'storeCounsellingBooking'])->name('counselling.bookings.store');
Route::patch('/counselling/bookings/{booking}', [AuthController::class, 'updateCounsellingBooking'])->name('counselling.bookings.update');
Route::post('/clinic/stock', [AuthController::class, 'storeClinicStock'])->name('clinic.stock.store');
Route::post('/clinic/stock/usage', [AuthController::class, 'storeClinicStockUsage'])->name('clinic.stock.usage.store');
Route::post('/clinic/stock/usage/{usage}/update', [AuthController::class, 'updateClinicStockUsage'])->name('clinic.stock.usage.update');
Route::post('/clinic/stock/usage/{usage}/delete', [AuthController::class, 'deleteClinicStockUsage'])->name('clinic.stock.usage.delete');
Route::post('/clinic/stock/{item}', [AuthController::class, 'updateClinicStock'])->name('clinic.stock.update');
Route::post('/clinic/stock/{item}/confirm', [AuthController::class, 'confirmClinicStock'])->name('clinic.stock.confirm');
Route::post('/clinic/stock/{item}/delete', [AuthController::class, 'deleteClinicStock'])->name('clinic.stock.delete');
Route::post('/clinic/stock/{item}/comments', [AuthController::class, 'commentClinicStock'])->name('clinic.stock.comment');
Route::get('/clinic/report/download', [AuthController::class, 'downloadClinicReport'])->name('clinic.report.download');
Route::get('/accommodation', [AuthController::class, 'accommodation'])->name('accommodation');
Route::get('/accommodation/report', [AuthController::class, 'accommodationReport'])->name('accommodation.report');
Route::get('/accommodation/report/download', [AuthController::class, 'downloadAccommodationReport'])->name('accommodation.report.download');
Route::get('/accommodation/pending-admissions', [AuthController::class, 'pendingAdmissions'])->name('student.accommodation.pending');
Route::get('/accommodation/rooms', [AuthController::class, 'roomManagement'])->name('student.accommodation.rooms');
Route::post('/accommodation/rooms', [AuthController::class, 'storeRoom'])->name('student.accommodation.rooms.store');
Route::post('/accommodation/rooms/defaults', [AuthController::class, 'seedDefaultRooms'])->name('student.accommodation.rooms.defaults');
Route::post('/accommodation/{application}/status', [AuthController::class, 'updateAdmissionStatus'])->name('student.accommodation.status');
Route::post('/accommodation/{application}/resend-email', [AuthController::class, 'resendAccommodationStatusEmail'])->name('student.accommodation.resend-email');
Route::get('/accommodation/apply', [AuthController::class, 'applyAccommodation'])->name('student.accommodation.apply');
Route::post('/accommodation/store', [AuthController::class, 'storeAccommodation'])->name('student.accommodation.store');
Route::get('/accommodation/checkout', [AuthController::class, 'checkoutAccommodation'])->name('student.accommodation.checkout');
Route::post('/accommodation/checkout', [AuthController::class, 'storeCheckout'])->name('student.accommodation.checkout.store');
Route::post('/accommodation/{application}/checkout/status', [AuthController::class, 'updateCheckoutStatus'])->name('student.accommodation.checkout.status');
Route::get('/home', [AuthController::class, 'dashboard'])->name('home');
Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
Route::get('/admin/users/create', [AuthController::class, 'showCreateUser'])->name('admin.users.create');
Route::post('/admin/users/store', [AuthController::class, 'storeAdminUser'])->name('admin.users.store');
Route::get('/password/temporary', [AuthController::class, 'showTemporaryPasswordForm'])->name('password.temporary');
Route::post('/password/temporary', [AuthController::class, 'updateTemporaryPassword'])->name('password.temporary.update');

Route::middleware(['auth'])->group(function () {
    Route::get('/academic/referrals', [AcademicReferralController::class, 'index'])->name('academic.referrals');
    Route::post('/academic/referrals', [AcademicReferralController::class, 'store'])->name('academic.referrals.store');
    Route::patch('/academic/referrals/{referral}/status', [AcademicReferralController::class, 'updateStatus'])->name('academic.referrals.status');
    Route::post('/academic/referrals/{referral}/attendance', [AcademicReferralController::class, 'saveAttendanceSheet'])->name('academic.referrals.attendance');
    Route::post('/academic/referrals/{referral}/comment', [AcademicReferralController::class, 'addComment'])->name('academic.referrals.comment');
    Route::get('/academic/referrals/report', [AcademicReferralController::class, 'generateReport'])->name('academic.referrals.report');
});

use Illuminate\Support\Facades\Mail;

Route::get('/send-test-email', function () {
    $testEmail = 'lrasebolu@gmail.com';
    $data = ['name' => 'Lerato'];

    try {
        Mail::send([], [], function ($message) use ($testEmail) {
            $message->to($testEmail)
                    ->subject('Test Email from My Laravel App')
                    ->html('<h1>Test Email Sent!</h1><p>Hello, this is a test email from my Laravel application using SendGrid.</p>');
        });
        return 'Test email sent to ' . $testEmail . '.';
    } catch (\Exception $e) {
        return 'Failed to send email: ' . $e->getMessage();
    }
});
