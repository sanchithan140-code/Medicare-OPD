<?php

// Render all outgoing email paths without a database or an SMTP connection.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$capture = new class (app('mail.manager')->mailer()) {
    public function __construct(private \Illuminate\Mail\Mailer $renderer) {}
    public function mailer(?string $name = null): \Illuminate\Mail\Mailer { return $this->renderer; }
    public array $messages = [];
    public function to(string $email): self { return $this; }
    public function send(\Illuminate\Mail\Mailable $message): void { $this->messages[] = $message; }
};
Illuminate\Support\Facades\Mail::swap($capture);
$service = new App\Services\HospitalMailService;
$user = new App\Models\User(['full_name' => "  Asha   Perera  ", 'email' => 'preview@example.test']);
$patient = (new App\Models\Patient(['first_name' => 'Asha', 'last_name' => 'Perera']))->setRelation('account', $user);
$doctor = (new App\Models\Doctor)
    ->setRelation('user', new App\Models\User(['full_name' => 'Dr. Example']))
    ->setRelation('specialty', new App\Models\Specialty);
$doctor->specialty->setAttribute('specialty_name', 'General Medicine');
$appointment = (new App\Models\Appointment(['appointment_no' => 'APT-PREVIEW-001', 'status' => 'CANCELLED']))
    ->setRelation('patient', $patient)
    ->setRelation('doctor', $doctor)
    ->setRelation('slot', new App\Models\AppointmentSlot(['slot_start' => '2026-09-10 10:00:00']));
$reminder = (new App\Models\AppointmentReminder)->setRelation('appointment', $appointment);
$order = (new App\Models\LabOrder(['lab_order_no' => 'LAB-PREVIEW-001']))
    ->setRelation('patient', $patient)->setRelation('doctor', $doctor);
$item = (new App\Models\LabOrderItem)->setRelation('order', $order)
    ->setRelation('test', new App\Models\LabTest(['test_name' => 'Blood count']))
    ->setRelation('result', null);

$service->otp($user, '012345', 'VERIFY_EMAIL');
$service->otp($user, '012345', 'RESET_PASSWORD');
$service->appointmentBooked($appointment);
$service->appointmentStatus($appointment, 'Your appointment was cancelled and the time slot was released.');
$service->reminder($reminder);
$service->doctorUnavailable($appointment);
$service->labResultReady($item);
$service->supportReply($user, '<script>alert("test")</script>');
$service->paymentReceipt($user, 'INV-PREVIEW-001', 'PAY-PREVIEW-001', 2500, 'CARD');

function check(bool $condition, string $message): void {
    if (!$condition) { throw new RuntimeException($message); }
}
check(count($capture->messages) === 9, 'Not all email paths produced a message.');
foreach ($capture->messages as $index => $message) {
    $html = $message->render();
    $content = $message->content();
    $text = view($content->text, array_merge([
        'heading' => $message->heading, 'lines' => $message->lines,
        'actionText' => $message->actionText, 'actionUrl' => $message->actionUrl,
    ], $content->with))->render();
    check(str_contains($html, 'Hello Asha,'), 'First-name HTML greeting missing.');
    check(str_contains($text, 'Hello Asha,'), 'First-name text greeting missing.');
    check(!str_contains($html, 'Asha Perera'), 'Full name leaked into greeting.');
    check(!str_contains($html, '<script>'), 'HTML content was not escaped.');
    check(!str_contains($html, 'View appointment'), 'Appointment-view button remains.');
    foreach ($content->with['details'] as $detail) {
        check(str_contains($html, e($detail['value'])), 'A detail value is missing.');
    }
    if ($index < 2) {
        check(str_contains($html, '012345'), 'OTP leading zero was lost.');
        check(str_contains($text, '012345'), 'Plain-text OTP missing.');
        check(str_contains($html, 'YOUR ONE-TIME CODE'), 'Code panel missing.');
    }
    if (in_array($index, [2, 3, 5], true)) {
        check($message->actionText === null && $message->actionUrl === null, 'Appointment CTA remains.');
    } else {
        check(str_contains($html, e($message->actionUrl)), 'Required action link missing.');
    }
    echo 'PASS: '.$message->heading.PHP_EOL;
}
echo "All 9 email paths rendered successfully; no emails sent.".PHP_EOL;
$service->appointmentBooked($appointment, 'Paid by card');
$service->appointmentBooked($appointment, 'Pay directly at hospital reception — payment pending. Amount due: LKR 2,500.00');
check(str_contains($capture->messages[9]->render(), 'Paid by card'), 'Card payment label missing.');
check(str_contains($capture->messages[10]->render(), 'payment pending'), 'Hospital payment must remain pending.');
check(!str_contains(file_get_contents(__DIR__.'/../app/Services/AppointmentBookingService.php'), '->appointmentBooked('), 'Reservation still sends booking email.');
echo "PASS: payment-choice email labels and no booking email during reservation.".PHP_EOL;
