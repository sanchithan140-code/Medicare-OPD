<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StaffBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('office_staff','admin','main_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required','integer','exists:patients,patient_id'],
            'slot_id' => ['required','integer','exists:appointment_slots,slot_id'],
            'booking_notes' => ['nullable','string','max:500'],
        ];
    }
}
