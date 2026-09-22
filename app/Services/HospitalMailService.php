<?php

namespace App\Services;

use App\Mail\HospitalMessage;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\LabOrderItem;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Throwable;

class HospitalMailService
{
    public function appointmentRefund(Appointment $appointment,float $amount,float $hospitalAmount=0): bool
    {
        $appointment->loadMissing('patient.account');
        $lines=[
            $this->greeting($appointment->patient->full_name),
            'Your appointment has been cancelled.',
            'Reference: '.$appointment->appointment_no,
            'Patient ID: '.$appointment->patient->patient_code,
        ];
        if($amount>0){$lines[]='Your online payment has been marked as refunded.';$lines[]='Amount: LKR '.number_format($amount,2);}
        if($hospitalAmount>0)$lines[]='Please collect your hospital payment refund of LKR '.number_format($hospitalAmount,2).' at reception. Bring your patient ID and payment receipt. This amount has not yet been refunded.';
        $lines[]='Status: '.($hospitalAmount>0?'HOSPITAL REFUND AWAITING COLLECTION':'REFUNDED');
        return $this->send($appointment->patient->account?->email,'MEDICARE refund - '.$appointment->appointment_no,'Appointment refund',$lines,'View payments',route('patient.payments'));
    }
    public function welcome(User $user, \App\Models\Patient $patient): bool
    {
        return $this->send(
            $user->email,
            'Welcome to MEDICARE — Your patient ID',
            'Welcome to MEDICARE',
            [
                $this->greeting($user->full_name),
                'Your email has been verified and your MEDICARE account is ready.',
                "Patient ID: {$patient->patient_code}",
                'Keep your patient ID handy when booking appointments or visiting Mediland Hospital.',
                'You can now book appointments and view your medical reports through the patient portal.',
                'Thank you for choosing Mediland Hospital.',
            ],
            'Open patient portal',
            route('patient.dashboard')
        );
    }

    public function otp(User $user, string $code, string $purpose): bool
    {
        $label = $purpose === 'RESET_PASSWORD' ? 'Password reset' : 'Email verification';

        return $this->send(
            $user->email,
            "MEDICARE {$label} OTP",
            $label,
            [
                $this->greeting($user->full_name),
                "Your one-time password is {$code}.",
                'This code expires in 3 minutes and can be used only once.',
                'Do not share this code with anyone, including hospital staff.',
            ],
            'Open MEDICARE',
            route($purpose === 'RESET_PASSWORD' ? 'password.reset.form' : 'verify.form', ['email' => $user->email])
        );
    }

    public function appointmentBooked(Appointment $appointment, ?string $paymentDescription = null): void
    {
        $appointment->loadMissing('patient.account','doctor.user','doctor.specialty','slot');
        $this->send(
            $appointment->patient->account?->email,
            "MEDICARE appointment {$appointment->appointment_no}",
            'Appointment booked',
            array_filter([
                $this->greeting($appointment->patient->full_name),
                "Reference: {$appointment->appointment_no}",
                "Patient ID: {$appointment->patient->patient_code}",
                "Doctor: {$appointment->doctor->user->full_name}",
                "Specialty: {$appointment->doctor->specialty->specialty_name}",
                'Date and time: '.$appointment->slot->slot_start->format('d M Y h:i A'),
                'Status: BOOKED — Office Staff will confirm the booking.',
                'Please arrive at least 15 minutes early and bring any relevant medical documents.',
                $paymentDescription ? 'Payment: '.$paymentDescription : null,
            ])
        );
    }

    public function appointmentStatus(Appointment $appointment, string $message): void
    {
        $appointment->loadMissing('patient.account','doctor.user','slot');
        $this->send(
            $appointment->patient->account?->email,
            'MEDICARE appointment update',
            'Appointment update',
            [
                $this->greeting($appointment->patient->full_name),
                $message,
                "Reference: {$appointment->appointment_no}",
                "Patient ID: {$appointment->patient->patient_code}",
                "Doctor: {$appointment->doctor->user->full_name}",
                'Date and time: '.$appointment->slot->slot_start->format('d M Y h:i A'),
                "Current status: {$appointment->status}",
            ]
        );
    }

    public function reminder(AppointmentReminder $reminder): bool
    {
        $reminder->loadMissing('appointment.patient.account','appointment.doctor.user','appointment.slot');
        $appointment = $reminder->appointment;
        if (! $appointment) {
            return false;
        }

        return $this->send(
            $appointment->patient->account?->email,
            'MEDICARE appointment reminder',
            'Upcoming appointment reminder',
            [
                $this->greeting($appointment->patient->full_name),
                "Reference: {$appointment->appointment_no}",
                "Patient ID: {$appointment->patient->patient_code}",
                "Doctor: {$appointment->doctor->user->full_name}",
                'Date and time: '.$appointment->slot->slot_start->format('d M Y h:i A'),
                'Please arrive at least 15 minutes early. Reschedule or cancel through the portal when you cannot attend.',
            ],
            'Manage appointment',
            route('patient.appointments')
        );
    }

    public function doctorUnavailable(Appointment $appointment): void
    {
        $this->appointmentStatus(
            $appointment,
            'The doctor is unavailable for this appointment period. Choose another date, request Office Staff assistance for a doctor from the same specialty, or cancel the booking. No transfer will be finalized without patient agreement.'
        );
    }

    public function labResultReady(LabOrderItem $item): void
    {
        $item->loadMissing('order.patient.account','order.doctor.user','test','result');
        $account = $item->order->patient->account;

        $this->send(
            $account?->email,
            'MEDICARE laboratory report available',
            'Your laboratory report is available',
            [
                $this->greeting($item->order->patient->full_name),
                "Test: {$item->test->test_name}",
                "Laboratory order: {$item->order->lab_order_no}",
                'The verified report is now available inside your secure MEDICARE account.',
                'For privacy, the medical report is not attached to this email.',
            ],
            'View laboratory reports',
            route('patient.labs')
        );
    }

    public function supportReply(User $user, string $subject): void
    {
        $this->send(
            $user->email,
            'MEDICARE support reply',
            'Hospital support replied',
            [
                $this->greeting($user->full_name),
                "A reply was added to your support request: {$subject}",
                'Sign in to MEDICARE to read the complete conversation securely.',
            ],
            'Open support',
            route('patient.support')
        );
    }

    public function paymentReceipt(User $user, string $invoiceNo, string $paymentNo, float $amount, string $method): void
    {
        $this->send(
            $user->email,
            "MEDICARE payment receipt {$paymentNo}",
            'Payment recorded',
            [
                $this->greeting($user->full_name),
                "Invoice: {$invoiceNo}",
                "Payment reference: {$paymentNo}",
                'Amount: LKR '.number_format($amount, 2),
                "Method: {$method}",
                'The receipt and updated balance are available in the secure portal.',
            ],
            'View payments',
            route('patient.payments')
        );
    }

    private function greeting(string $fullName): string
    {
        $firstName = preg_split('/\s+/u', trim($fullName), 2)[0] ?? '';

        return $firstName !== '' ? "Hello {$firstName}," : 'Hello,';
    }

    private function send(?string $email, string $subject, string $heading, array $lines, ?string $actionText = null, ?string $actionUrl = null): bool
    {
        if (! $email || str_ends_with($email, '@walkin.invalid')) {
            return false;
        }

        try {
            Mail::to($email)->send(new HospitalMessage($subject, $heading, $lines, $actionText, $actionUrl));
            return true;
        } catch (Throwable $exception) {
            report($exception);
            return false;
        }
    }
}
