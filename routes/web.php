<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\LabController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\SecureDocumentController;
use App\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::post('/assistant/chat', [PublicController::class, 'chatbot'])
    ->middleware('throttle:20,1')
    ->name('assistant.chat');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,10')->name('register.store');
    Route::get('/verify-email', [AuthController::class, 'showVerify'])->name('verify.form');
    Route::post('/verify-email', [AuthController::class, 'verify'])->middleware('throttle:10,10')->name('verify.store');
    Route::post('/verify-email/resend', [AuthController::class, 'resend'])->middleware('throttle:3,10')->name('verify.resend');
    Route::get('/forgot-password', [AuthController::class, 'showForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'forgot'])->middleware('throttle:3,10,password-otp:')->name('password.email');
    Route::get('/reset-password', [AuthController::class, 'showReset'])->name('password.reset.form');
    Route::post('/reset-password', [AuthController::class, 'reset'])->middleware('throttle:10,10')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::get('/notifications', [AccountController::class, 'notifications'])->name('account.notifications');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');
    Route::get('/secure-documents/{attachment}/view', [SecureDocumentController::class, 'view'])->name('documents.view');
    Route::get('/secure-documents/{attachment}/download', [SecureDocumentController::class, 'download'])->name('documents.download');
});

Route::middleware(['auth', 'role:patient', \App\Http\Middleware\PendingPatientLink::class])->prefix('patient')->name('patient.')->group(function () {
    Route::get('/dashboard', [PatientController::class, 'dashboard'])->name('dashboard');
    Route::get('/profiles', [PatientController::class, 'profiles'])->name('profiles');
    Route::post('/profiles', [PatientController::class, 'storeProfile'])->name('profiles.store');
    Route::get('/profiles/{patient}/edit', [PatientController::class, 'editProfile'])->name('profiles.edit');
    Route::put('/profiles/{patient}', [PatientController::class, 'updateProfile'])->name('profiles.update');
    Route::get('/doctors', [PatientController::class, 'doctors'])->name('doctors');
    Route::get('/ai-health-assistant', [AiController::class, 'show'])->name('ai');
    Route::post('/ai-health-assistant', [AiController::class, 'recommend'])->middleware('throttle:15,10')->name('ai.recommend');
    Route::get('/appointments', [PatientController::class, 'appointments'])->name('appointments');
    Route::get('/appointments/book', [PatientController::class, 'bookForm'])->name('appointments.book');
    Route::get('/appointment-slots', [PatientController::class, 'slots'])->name('slots');
    Route::post('/appointments', [PatientController::class, 'book'])->middleware('throttle:10,5')->name('appointments.store');
    Route::get('/appointments/{appointment}/payment', [PatientController::class, 'appointmentPayment'])->name('appointments.payment');
    Route::post('/invoices/{invoice}/card-payment', [PatientController::class, 'payInvoiceByCard'])->middleware('throttle:5,5')->name('payments.card');
    Route::post('/invoices/{invoice}/pay-at-hospital', [PatientController::class, 'payInvoiceAtHospital'])->name('payments.hospital');
    Route::get('/appointments/{appointment}/reschedule', [PatientController::class, 'rescheduleForm'])->name('appointments.reschedule');
    Route::post('/appointments/{appointment}/reschedule', [PatientController::class, 'reschedule'])->name('appointments.reschedule.store');
    Route::post('/appointments/{appointment}/cancel', [PatientController::class, 'cancel'])->name('appointments.cancel');
    Route::get('/history', [PatientController::class, 'history'])->name('history');
    Route::get('/laboratory', [PatientController::class, 'labs'])->name('labs');
    Route::get('/payments', [PatientController::class, 'payments'])->name('payments');
    Route::get('/insurance', [PatientController::class, 'insurance'])->name('insurance');
    Route::get('/notifications', [PatientController::class, 'notifications'])->name('notifications');
    Route::get('/support', [PatientController::class, 'support'])->name('support');
    Route::post('/support', [PatientController::class, 'storeSupport'])->name('support.store');
    Route::post('/support/{thread}/reply', [PatientController::class, 'replySupport'])->name('support.reply');
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/appointments', [DoctorController::class, 'appointments'])->name('appointments');
    Route::get('/patients/{patient}/history', [DoctorController::class, 'history'])->name('history');
    Route::get('/patient-records', [DoctorController::class, 'patientRecords'])->name('patients');
    Route::get('/appointments/{appointment}/consultation', [DoctorController::class, 'consultationForm'])->name('consultation');
    Route::post('/appointments/{appointment}/consultation', [DoctorController::class, 'storeConsultation'])->name('consultation.store');
    Route::get('/schedule', [DoctorController::class, 'schedule'])->name('schedule');
    Route::post('/leave', [DoctorController::class, 'requestLeave'])->name('leave.store');
});

Route::middleware(['auth', 'role:lab_officer'])->prefix('laboratory')->name('lab.')->group(function () {
    Route::get('/dashboard', [LabController::class, 'dashboard'])->name('dashboard');
    Route::get('/orders', [LabController::class, 'orders'])->name('orders');
    Route::post('/orders/{order}/status', [LabController::class, 'updateStatus'])->name('orders.status');
    Route::get('/items/{item}/result', [LabController::class, 'resultForm'])->name('result');
    Route::post('/items/{item}/result', [LabController::class, 'storeResult'])->name('result.store');
});

Route::middleware(['auth', 'role:office_staff,admin,main_admin'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
    Route::get('/walk-in', [\App\Http\Controllers\WalkInController::class, 'index'])->name('walk-in');
    Route::get('/walk-in-receipts', [\App\Http\Controllers\WalkInController::class, 'receipts'])->name('walkin-receipts');
    Route::post('/walk-in', [\App\Http\Controllers\WalkInController::class, 'store'])->name('walk-in.store');
    Route::get('/receipts/{invoice}', [\App\Http\Controllers\WalkInController::class, 'receipt'])->name('receipt');
    Route::get('/patient-links', [\App\Http\Controllers\WalkInController::class, 'links'])->name('patient-links');
    Route::post('/patient-links/{user}', [\App\Http\Controllers\WalkInController::class, 'link'])->name('patient-links.store');
    Route::get('/appointments', [StaffController::class, 'appointments'])->name('appointments');
    Route::get('/schedules', [StaffController::class, 'schedules'])->name('schedules');
    Route::post('/schedules', [StaffController::class, 'createSchedule'])->name('schedules.store');
    Route::get('/appointments/book', [StaffController::class, 'bookingForm'])->name('appointments.book');
    Route::post('/appointments/book', [StaffController::class, 'storeBooking'])->name('appointments.book.store');
    Route::post('/appointments/{appointment}/status', [StaffController::class, 'changeStatus'])->name('appointments.status');
    Route::post('/schedules/{schedule}/confirm-appointments', [StaffController::class, 'confirmSession'])->name('appointments.confirm-session');
    Route::get('/patients', [StaffController::class, 'patients'])->name('patients');
    Route::post('/patients', [StaffController::class, 'storePatient'])->name('patients.store');
    Route::get('/billing', [StaffController::class, 'billing'])->name('billing');
    Route::post('/billing', [StaffController::class, 'createInvoice'])->name('billing.store');
    Route::post('/billing/{invoice}/payment', [StaffController::class, 'recordPayment'])->name('payments.store');
    Route::post('/billing/{invoice}/refunds/{payment}/collect', [StaffController::class, 'confirmHospitalRefund'])->name('refunds.collect');
    Route::get('/insurance', [StaffController::class, 'insurance'])->name('insurance');
    Route::post('/insurance', [StaffController::class, 'createClaim'])->name('insurance.store');
    Route::post('/insurance/{claim}', [StaffController::class, 'updateClaim'])->name('insurance.update');
    Route::get('/support', [StaffController::class, 'support'])->name('support');
    Route::post('/support/{thread}/reply', [StaffController::class, 'replySupport'])->name('support.reply');
});

Route::middleware(['auth', 'role:admin,main_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::post('/users', [AdminController::class, 'createUser'])->name('users.store');
    Route::post('/users/{user}/toggle', [AdminController::class, 'toggleUser'])->name('users.toggle');
    Route::get('/doctors', [AdminController::class, 'doctors'])->name('doctors');
    Route::post('/doctors', [AdminController::class, 'createDoctor'])->name('doctors.store');
    Route::post('/schedules', [AdminController::class, 'createSchedule'])->name('schedules.store');
    Route::post('/leave/{leave}', [AdminController::class, 'decideLeave'])->name('leave.decide');
    Route::get('/catalogs', [AdminController::class, 'catalogs'])->name('catalogs');
    Route::post('/catalogs', [AdminController::class, 'storeCatalog'])->name('catalogs.store');
    Route::get('/analytics', [AdminController::class, 'analytics'])->name('analytics');
    Route::post('/analytics/generate', [AdminController::class, 'generateTrends'])->name('analytics.generate');
    Route::get('/audit', [AdminController::class, 'audit'])->name('audit');
});

Route::middleware(['auth','role:office_staff,admin,main_admin'])->group(function(){
    Route::get('/staff/lab-bookings',[\App\Http\Controllers\LabBookingController::class,'index'])->name('staff.lab-bookings');
    Route::post('/staff/lab-bookings',[\App\Http\Controllers\LabBookingController::class,'store'])->name('staff.lab-bookings.store');
    Route::post('/staff/lab-bookings/{order}/edit',[\App\Http\Controllers\LabBookingController::class,'edit'])->name('staff.lab-bookings.edit');
    Route::post('/staff/lab-prices/{test}',[\App\Http\Controllers\LabBookingController::class,'price'])->name('staff.lab-prices');
});
Route::middleware(['auth','role:office_staff,lab_officer,admin,main_admin'])->group(function(){
    Route::get('/lab-payments/{order}',[\App\Http\Controllers\LabBookingController::class,'payment'])->name('lab-payment.show');
    Route::post('/lab-payments/{order}',[\App\Http\Controllers\LabBookingController::class,'pay'])->name('lab-payment.store');
    Route::get('/lab-receipts/{invoice}',[\App\Http\Controllers\WalkInController::class,'receipt'])->name('lab-payment.receipt');
});
