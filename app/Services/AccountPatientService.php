<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AccountPatientService
{
    public function ensure(User $user): Patient
    {
        return DB::transaction(function () use ($user) {
            $account = User::whereKey($user->getKey())->lockForUpdate()->firstOrFail();
            abort_if($account->requested_patient_code !== null,403,'Reception must verify your existing patient record first.');
            $patient = $account->patients()->where('relationship_to_account', 'Self')
                ->orderByDesc('active')->orderBy('patient_id')->first();

            if ($patient) {
                if (! $patient->active) {
                    $patient->update(['active' => true]);
                }
                return $patient;
            }

            $names = preg_split('/\s+/u', trim($account->full_name), 2);

            return $account->patients()->create([
                'patient_code' => app(PatientCodeService::class)->generate(),
                'relationship_to_account' => 'Self',
                'first_name' => $names[0],
                'last_name' => $names[1] ?? '',
                'sex' => 'UNKNOWN',
                'email' => $account->email,
                'phone' => $account->phone,
                'active' => true,
                'created_by' => $account->user_id,
            ]);
        });
    }
}
