<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isRole('office_staff','admin','main_admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'payment_method'=>['required', Rule::in(['CASH','CARD','INSURANCE','ONLINE_DEMO'])],
            'amount'=>['required','numeric','min:0.01','max:99999999.99'],
            'transaction_reference'=>['nullable','required_if:payment_method,CARD,ONLINE_DEMO','string','max:100'],
            'confirm_payment'=>['required','accepted'],
        ];
    }
}
