<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientProfileRequest;
use App\Models\AiRecommendation;
use App\Models\Appointment;
use App\Models\AppointmentSlot;
use App\Models\AppointmentStatusHistory;
use App\Models\Doctor;
use App\Models\InsuranceClaim;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\LabOrder;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Specialty;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\WeeklyDiseaseTrend;
use App\Services\AppointmentBookingService;
use App\Services\AuditService;
use App\Services\PatientCodeService;
use App\Services\HospitalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PatientController extends Controller
{
    public function __construct(
        private readonly PatientCodeService $codes,
        private readonly AppointmentBookingService $booking,
        private readonly AuditService $audit,
        private readonly HospitalMailService $mail,
    ) {}

    public function dashboard(Request $request)
    {
        app(\App\Services\AccountPatientService::class)->ensure($request->user());
        $userId = $request->user()->user_id;
        $patientIds = $request->user()->patients()->pluck('patient_id');

        return view('patient.dashboard', [
            'patients' => $request->user()->patients()->where('active', true)->get(),
            'upcoming' => Appointment::with('patient','doctor.user','doctor.specialty','slot')
                ->whereIn('patient_id', $patientIds)
                ->whereIn('status', ['BOOKED','CONFIRMED','RESCHEDULED','CHECKED_IN'])
                ->whereHas('slot', fn ($q) => $q->where('slot_start', '>=', now()))
                ->orderByDesc('appointment_id')
                ->limit(5)->get(),
            'notifications' => Notification::where('user_id', $userId)->latest('notification_id')->limit(6)->get(),
            'trends' => WeeklyDiseaseTrend::with('condition')->latest('week_start')->orderByDesc('case_count')->limit(6)->get(),
        ]);
    }

    public function profiles(Request $request)
    {
        app(\App\Services\AccountPatientService::class)->ensure($request->user());
        return view('patient.profiles', ['patients' => $request->user()->patients()->orderByDesc('patient_id')->get()]);
    }

    public function storeProfile(PatientProfileRequest $request)
    {
        $data = $request->validated();

        $patient = DB::transaction(function () use ($request, $data) {
            $patient = Patient::create([
                'account_user_id' => $request->user()->user_id,
                'patient_code' => $this->codes->generate(),
                'relationship_to_account' => $data['relationship_to_account'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'dob' => $data['dob'] ?? null,
                'sex' => $data['sex'],
                'nic_passport' => $data['nic_passport'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
                'created_by' => $request->user()->user_id,
            ]);

            if (! empty($data['emergency_contact_name']) && ! empty($data['emergency_contact_phone'])) {
                DB::table('patient_emergency_contacts')->insert([
                    'patient_id' => $patient->patient_id,
                    'contact_name' => $data['emergency_contact_name'],
                    'phone' => $data['emergency_contact_phone'],
                    'is_primary' => 1,
                    'created_at' => now(),
                ]);
            }

            if (! empty($data['allergen'])) {
                DB::table('patient_allergies')->insert([
                    'patient_id' => $patient->patient_id,
                    'allergen' => $data['allergen'],
                    'reaction' => $data['allergy_reaction'] ?? null,
                    'severity' => 'UNKNOWN',
                    'created_at' => now(),
                ]);
            }

            return $patient;
        });

        $this->audit->record('patient.created', $patient, [], $patient->toArray());

        return back()->with('success', "Patient profile created. Permanent patient ID: {$patient->patient_code}");
    }

    public function editProfile(Request $request, Patient $patient)
    {
        $this->ensureOwnedPatient($request, $patient);

        return view('patient.profile-edit', [
            'patient' => $patient,
            'emergencyContact' => DB::table('patient_emergency_contacts')
                ->where('patient_id', $patient->patient_id)->orderByDesc('is_primary')->orderBy('emergency_contact_id')->first(),
            'allergy' => DB::table('patient_allergies')
                ->where('patient_id', $patient->patient_id)->orderBy('allergy_id')->first(),
        ]);
    }

    public function updateProfile(PatientProfileRequest $request, Patient $patient)
    {
        $this->ensureOwnedPatient($request, $patient);
        $data = $request->validated();
        $before = $patient->toArray();

        DB::transaction(function () use ($request, $patient, $data) {
            $patient->update([
                'relationship_to_account' => $data['relationship_to_account'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'dob' => $data['dob'] ?? null,
                'sex' => $data['sex'],
                'nic_passport' => $data['nic_passport'] ?? null,
                'email' => $data['email'] ?? null,
                'phone' => $data['phone'] ?? null,
                'address' => $data['address'] ?? null,
                'blood_group' => $data['blood_group'] ?? null,
            ]);

            $contact = DB::table('patient_emergency_contacts')
                ->where('patient_id', $patient->patient_id)->orderByDesc('is_primary')->orderBy('emergency_contact_id')->first();
            if (! empty($data['emergency_contact_name']) && ! empty($data['emergency_contact_phone'])) {
                $contactData = [
                    'patient_id' => $patient->patient_id,
                    'contact_name' => $data['emergency_contact_name'],
                    'phone' => $data['emergency_contact_phone'],
                    'is_primary' => 1,
                ];
                $contact
                    ? DB::table('patient_emergency_contacts')->where('emergency_contact_id', $contact->emergency_contact_id)->update($contactData)
                    : DB::table('patient_emergency_contacts')->insert($contactData + ['created_at' => now()]);
            } elseif ($contact) {
                DB::table('patient_emergency_contacts')->where('emergency_contact_id', $contact->emergency_contact_id)->delete();
            }

            $allergy = DB::table('patient_allergies')
                ->where('patient_id', $patient->patient_id)->orderBy('allergy_id')->first();
            if (! empty($data['allergen'])) {
                $allergyData = [
                    'patient_id' => $patient->patient_id,
                    'allergen' => $data['allergen'],
                    'reaction' => $data['allergy_reaction'] ?? null,
                    'severity' => 'UNKNOWN',
                ];
                $allergy
                    ? DB::table('patient_allergies')->where('allergy_id', $allergy->allergy_id)->update($allergyData)
                    : DB::table('patient_allergies')->insert($allergyData + ['created_at' => now()]);
            } elseif ($allergy) {
                DB::table('patient_allergies')->where('allergy_id', $allergy->allergy_id)->delete();
            }
        });

        $patient->refresh();
        $this->audit->record('patient.updated', $patient, $before, $patient->toArray(), $request);

        return redirect()->route('patient.profiles')->with('success', "Profile {$patient->patient_code} updated successfully.");
    }

    private function ensureOwnedPatient(Request $request, Patient $patient): void
    {
        abort_unless($patient->account_user_id === $request->user()->user_id, 403);
    }

    public function doctors(Request $request)
    {
        $doctors = Doctor::with('user','specialty')
            ->where('active', true)
            ->when($request->filled('q'), function ($q) use ($request) {
                $term = '%'.$request->string('q').'%';
                $q->where(function ($sub) use ($term) {
                    $sub->whereHas('user', fn ($u) => $u->where('full_name','like',$term))
                        ->orWhereHas('specialty', fn ($s) => $s->where('specialty_name','like',$term))
                        ->orWhere('qualifications_summary','like',$term);
                });
            })
            ->when($request->integer('specialty_id'), fn ($q, $id) => $q->where('specialty_id',$id))
            ->orderBy('consultation_fee')
            ->paginate(12)->withQueryString();

        return view('patient.doctors', [
            'doctors' => $doctors,
            'specialties' => Specialty::where('active',true)->orderBy('specialty_name')->get(),
        ]);
    }

    public function appointments(Request $request)
    {
        $patientIds = $request->user()->patients()->pluck('patient_id');
        return view('patient.appointments', [
            'appointments' => Appointment::with('patient','doctor.user','doctor.specialty','slot')
                ->whereIn('patient_id',$patientIds)
                ->latest('appointment_id')
                ->paginate(15),
        ]);
    }

    public function bookForm(Request $request)
    {
        $accountPatient = app(\App\Services\AccountPatientService::class)->ensure($request->user());
        $patients = $request->user()->patients()->where('active', true)->get()
            ->sortBy(fn ($patient) => $patient->patient_id === $accountPatient->patient_id ? 0 : 1)->values();
        $doctorId = $request->integer('doctor_id') ?: null;
        $rangeStart = now()->startOfDay();
        $rangeEnd = $rangeStart->copy()->addDays(20);
        $availability = $doctorId
            ? $this->booking->availableSlotsBetween($doctorId, $rangeStart->toDateString(), $rangeEnd->toDateString())
                ->groupBy(fn ($slot) => $slot->slot_start->toDateString())
            : collect();
        $requestedDate = (string) $request->query('date', '');
        $selectedDate = $availability->has($requestedDate) ? $requestedDate : null;

        return view('patient.book', [
            'patients' => $patients,
            'accountPatientId' => $accountPatient->patient_id,
            'doctors' => Doctor::with('user','specialty')->where('active',true)->get(),
            'selectedDoctor' => $doctorId ? Doctor::with('user','specialty')->find($doctorId) : null,
            'availability' => $availability,
            'slots' => $selectedDate ? $availability->get($selectedDate, collect()) : collect(),
            'selectedDate' => $selectedDate,
            'rangeStart' => $rangeStart,
            'rangeEnd' => $rangeEnd,
            'recommendationId' => $request->integer('recommendation_id') ?: null,
        ]);
    }

    public function slots(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => ['nullable','integer','exists:doctors,doctor_id'],
            'date' => ['nullable','date','after_or_equal:today'],
        ]);

        return response()->json([
            'slots' => $this->booking->availableSlots($data['doctor_id'] ?? null, $data['date'] ?? null)
                ->map(fn ($slot) => [
                    'slot_id' => $slot->slot_id,
                    'start' => $slot->slot_start->format('Y-m-d H:i:s'),
                    'end' => $slot->slot_end->format('Y-m-d H:i:s'),
                    'doctor' => $slot->schedule->doctor->user->full_name,
                    'specialty' => $slot->schedule->doctor->specialty->specialty_name,
                    'fee' => $slot->schedule->doctor->consultation_fee,
                ]),
        ]);
    }

    public function book(Request $request)
    {
        $data = $request->validate([
            'patient_id' => ['required','integer','exists:patients,patient_id'],
            'slot_id' => ['required','integer','exists:appointment_slots,slot_id'],
            'recommendation_id' => ['nullable','integer','exists:ai_recommendations,recommendation_id'],
        ]);

        $patient = Patient::where('patient_id',$data['patient_id'])
            ->where('account_user_id',$request->user()->user_id)
            ->where('active',true)->firstOrFail();

        if (! empty($data['recommendation_id'])) {
            $validRecommendation = AiRecommendation::where('recommendation_id',$data['recommendation_id'])
                ->where('user_id',$request->user()->user_id)
                ->where(function ($query) use ($patient) {
                    $query->whereNull('patient_id')->orWhere('patient_id',$patient->patient_id);
                })->exists();
            if (! $validRecommendation) {
                throw ValidationException::withMessages(['recommendation_id'=>'The selected care recommendation does not belong to this patient account.']);
            }
        }

        $appointment = $this->booking->book(
            $patient,
            $data['slot_id'],
            $request->user()->user_id,
            ! empty($data['recommendation_id']) ? 'AI' : 'PATIENT',
            $data['recommendation_id'] ?? null,
        );

        $invoice = DB::transaction(function () use ($appointment, $patient, $request) {
            $invoice = Invoice::firstOrCreate(
                ['appointment_id' => $appointment->appointment_id],
                [
                    'invoice_no'=>'INV-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),
                    'patient_id'=>$patient->patient_id,
                    'subtotal'=>$appointment->consultation_fee,
                    'discount_amount'=>0,
                    'insurance_covered'=>0,
                    'total_amount'=>$appointment->consultation_fee,
                    'paid_amount'=>0,
                    'balance_amount'=>$appointment->consultation_fee,
                    'status'=>'ISSUED',
                    'issued_at'=>now(),
                    'created_by'=>$request->user()->user_id,
                ]
            );
            $invoice->items()->firstOrCreate(
                ['item_type'=>'CONSULTATION'],
                [
                    'description'=>'Doctor consultation – '.$appointment->doctor->user->full_name,
                    'quantity'=>1,
                    'unit_price'=>$appointment->consultation_fee,
                    'line_total'=>$appointment->consultation_fee,
                ]
            );
            return $invoice;
        });

        return redirect()->route('patient.appointments.payment',$appointment)
            ->with('success', "Appointment {$appointment->appointment_no} reserved. Choose a payment method.");
    }

    public function appointmentPayment(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->patient()->where('account_user_id',$request->user()->user_id)->exists(),403);
        $appointment->load('patient','doctor.user','doctor.specialty','slot');
        $invoice = Invoice::with('payments')->where('appointment_id',$appointment->appointment_id)->firstOrFail();
        return view('patient.appointment-payment',compact('appointment','invoice'));
    }

    public function payInvoiceByCard(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->patient()->where('account_user_id',$request->user()->user_id)->exists(),403);
        $request->merge(['card_number'=>preg_replace('/[\s-]+/','',(string)$request->input('card_number')),'expiry'=>trim((string)$request->input('expiry'))]);
        $request->validate([
            'cardholder_name'=>['required','string','max:100'],
            'card_number'=>['bail','required','digits:16',function($attribute,$value,$fail){if(!\App\Services\CardInput::validNumber($value))$fail('Enter a valid 16-digit card number.');}],
            'expiry'=>['required','string',function($attribute,$value,$fail){if(!\App\Services\CardInput::validExpiry($value))$fail('Enter a valid, unexpired date in MM/YY format.');}],
            'cvv'=>['required','digits_between:3,4'],
        ]);
        $payment=DB::transaction(function()use($invoice,$request){
            $locked=Invoice::whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status,['ISSUED','PARTIALLY_PAID']) && $locked->balance_amount>0,422,'This invoice is already paid or closed.');
            $amount=$locked->balance_amount;
            $payment=Payment::create([
                'payment_no'=>'PAY-'.strtoupper(bin2hex(random_bytes(8))),
                'invoice_id'=>$locked->invoice_id,'payment_method'=>'CARD','amount'=>$amount,
                'transaction_reference'=>'DEMO-CARD-'.strtoupper(bin2hex(random_bytes(6))),
                'status'=>'COMPLETED','received_by'=>$request->user()->user_id,'paid_at'=>now(),
                'notes'=>'Project demonstration card payment; no bank charge',
            ]);
            $locked->update(['paid_amount'=>(float)$locked->paid_amount+(float)$amount,'balance_amount'=>0,'status'=>'PAID']);
            return $payment;
        });
        $this->audit->record('payment.demo_card_completed',$invoice,[],['payment_id'=>$payment->payment_id,'amount'=>$payment->amount]);
        $invoice->load('patient.account','appointment');
        if($invoice->appointment)$this->mail->appointmentBooked($invoice->appointment,'Paid by card');
        if($invoice->patient?->account)$this->mail->paymentReceipt($invoice->patient->account,$invoice->invoice_no,$payment->payment_no,(float)$payment->amount,'CARD');
        return redirect()->route('patient.payments')->with('success','Card payment completed. Your receipt is available.');
    }
    public function payInvoiceAtHospital(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->patient()->where('account_user_id',$request->user()->user_id)->exists(),403);
        abort_if((float)$invoice->balance_amount <= 0,422,'This invoice has already been paid.');
        if ($invoice->appointment) {
            $this->mail->appointmentBooked(
                $invoice->appointment,
                'Pay directly at hospital reception — payment pending. Amount due: LKR '.number_format($invoice->balance_amount, 2)
            );
        }
        return redirect()->route('patient.appointments')->with('success',"Pay at hospital selected. Show patient ID {$invoice->patient->patient_code} at reception.");
    }

    public function cancel(Request $request, Appointment $appointment)
    {
        abort_unless(
            $appointment->patient()->where('account_user_id',$request->user()->user_id)->exists(),
            403
        );

        $data = $request->validate(['reason' => ['required','string','max:500']]);

        $refunded=DB::transaction(function () use ($request, $appointment, $data) {
            $locked=Appointment::whereKey($appointment->appointment_id)->lockForUpdate()->firstOrFail();
            $appointment->setRawAttributes($locked->getAttributes(),true);
            abort_unless($appointment->allowsPatientChanges(),422,'Cancellation is available only within seven days of the original booking and before consultation.');
            $old = $appointment->status;
            abort_unless(in_array($old,['BOOKED','CONFIRMED','RESCHEDULED'],true), 422, 'This appointment cannot be cancelled.');

            $appointment->update([
                'status' => 'CANCELLED',
                'cancellation_reason' => $data['reason'],
                'cancelled_at' => now(),
            ]);
            AppointmentSlot::where('slot_id',$appointment->slot_id)->update(['slot_status'=>'AVAILABLE']);
            $this->booking->skipPendingReminders($appointment, 'Appointment cancelled');
            $refunded=app(\App\Services\AppointmentInvoiceService::class)->refundCancellation($appointment);
            AppointmentStatusHistory::create([
                'appointment_id'=>$appointment->appointment_id,
                'from_status'=>$old,
                'to_status'=>'CANCELLED',
                'reason'=>$data['reason'],
                'changed_by'=>$request->user()->user_id,
            ]);
            return $refunded;
        });

        $this->audit->record('appointment.cancelled',$appointment,[],['reason'=>$data['reason']]);
        $this->mail->appointmentStatus($appointment, 'Your appointment was cancelled and the time slot was released.');
        if($refunded['online']>0 || $refunded['hospital']>0)$this->mail->appointmentRefund($appointment,$refunded['online'],$refunded['hospital']);

        return back()->with('success','Appointment cancelled.'.($refunded['online']>0?' Online refund recorded: LKR '.number_format($refunded['online'],2).'.':'').($refunded['hospital']>0?' Please collect LKR '.number_format($refunded['hospital'],2).' from hospital reception.':''));
    }


    public function rescheduleForm(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->patient()->where('account_user_id',$request->user()->user_id)->exists(),403);
        abort_unless($appointment->allowsPatientChanges(),422,'Rescheduling is available only within seven days of the original booking and before consultation.');
        abort_unless(in_array($appointment->status,['BOOKED','CONFIRMED','RESCHEDULED'],true),422,'This appointment cannot be rescheduled.');
        return view('patient.reschedule',[
            'appointment'=>$appointment->load('patient','doctor.user','doctor.specialty','slot'),
            'slots'=>AppointmentSlot::with('schedule')->where('slot_status','AVAILABLE')->withoutApprovedLeave()->where('slot_start','>',now())
                ->whereHas('schedule',fn($q)=>$q->where('doctor_id',$appointment->doctor_id)->where('status','OPEN')->notStarted())
                ->orderBy('slot_start')->limit(40)->get(),
        ]);
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        abort_unless($appointment->patient()->where('account_user_id',$request->user()->user_id)->exists(),403);
        $data=$request->validate(['slot_id'=>['required','integer','exists:appointment_slots,slot_id']]);
        DB::transaction(function() use($request,$appointment,$data){
            $locked=Appointment::where('appointment_id',$appointment->appointment_id)->lockForUpdate()->firstOrFail();
            abort_unless($locked->allowsPatientChanges(),422,'Rescheduling is available only within seven days of the original booking and before consultation.');
            Patient::where('patient_id',$locked->patient_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status,['BOOKED','CONFIRMED','RESCHEDULED'],true),422,'This appointment cannot be rescheduled.');
            $newSlot=AppointmentSlot::with('schedule')->where('slot_id',$data['slot_id'])->lockForUpdate()->firstOrFail();
            abort_unless(AppointmentSlot::whereKey($newSlot->slot_id)->withoutApprovedLeave()->exists(),422,'This doctor is on approved leave at the selected time.');
            abort_unless($newSlot->slot_status==='AVAILABLE' && $newSlot->schedule->doctor_id===$locked->doctor_id && $newSlot->slot_start->isFuture() && $newSlot->schedule->acceptsBookings(),422,'The selected slot is no longer available.');
            $patientConflict = Appointment::where('patient_id',$locked->patient_id)
                ->where('appointment_id','!=',$locked->appointment_id)
                ->where('doctor_id',$locked->doctor_id)
                ->where('status','!=','CANCELLED')
                ->whereHas('slot',fn($query)=>$query->whereDate('slot_start',$newSlot->slot_start->toDateString()))
                ->exists();
            abort_if($patientConflict,422,'This patient already has another appointment with this doctor on the selected date.');
            AppointmentSlot::where('slot_id',$locked->slot_id)->update(['slot_status'=>'AVAILABLE']);
            $old=$locked->status;
            $locked->update(['schedule_id'=>$newSlot->schedule_id,'slot_id'=>$newSlot->slot_id,'status'=>'RESCHEDULED','confirmed_at'=>null]);
            $newSlot->update(['slot_status'=>'BOOKED']);
            AppointmentStatusHistory::create(['appointment_id'=>$locked->appointment_id,'from_status'=>$old,'to_status'=>'RESCHEDULED','reason'=>'Patient selected a new appointment slot','changed_by'=>$request->user()->user_id]);
        });
        $appointment->refresh()->load('patient.account','doctor.user','slot');
        $this->booking->refreshPendingReminders($appointment);
        $this->audit->record('appointment.rescheduled',$appointment);
        $this->mail->appointmentStatus($appointment,'Your appointment was rescheduled. Office Staff may reconfirm the updated booking.');
        return redirect()->route('patient.appointments')->with('success','Appointment rescheduled and the previous slot was released.');
    }

    public function history(Request $request)
    {
        $patientIds = $request->user()->patients()->pluck('patient_id');
        $consultations = \App\Models\Consultation::with('patient','doctor.user','conditions','prescriptions.items','labOrders.items.test','appointment')
            ->whereIn('patient_id',$patientIds)->latest('consultation_id')->paginate(10);

        return view('patient.history',compact('consultations'));
    }

    public function labs(Request $request)
    {
        $patientIds = $request->user()->patients()->pluck('patient_id');
        $orders = LabOrder::with('patient','doctor.user','items.test','items.result.attachments')
            ->whereIn('patient_id',$patientIds)->latest('lab_order_id')->paginate(10);

        return view('patient.labs',compact('orders'));
    }

    public function payments(Request $request)
    {
        $patientIds = $request->user()->patients()->pluck('patient_id');
        $invoices = Invoice::with('patient','items','payments')
            ->whereIn('patient_id',$patientIds)->latest('invoice_id')->paginate(12);

        return view('patient.payments',compact('invoices'));
    }

    public function insurance(Request $request)
    {
        $patientIds = $request->user()->patients()->pluck('patient_id');
        $claims = InsuranceClaim::with('provider','invoice')
            ->whereIn('patient_id',$patientIds)->latest('claim_id')->paginate(12);

        return view('patient.insurance',compact('claims'));
    }

    public function notifications(Request $request)
    {
        Notification::where('user_id',$request->user()->user_id)->whereNull('read_at')->update(['read_at'=>now()]);
        return view('patient.notifications',[
            'notifications'=>Notification::where('user_id',$request->user()->user_id)->latest('notification_id')->paginate(20),
        ]);
    }

    public function support(Request $request)
    {
        $threads = SupportThread::with('messages')
            ->where('user_id',$request->user()->user_id)
            ->latest('updated_at')->get();

        return view('patient.support',compact('threads'));
    }

    public function storeSupport(Request $request)
    {
        $data=$request->validate([
            'patient_id'=>['nullable','integer'],
            'subject'=>['required','string','max:180'],
            'category'=>['required','in:APPOINTMENT,OTP_LOGIN,PAYMENT,LAB_REPORT,INSURANCE,OTHER'],
            'message'=>['required','string','max:5000'],
        ]);

        if (! empty($data['patient_id'])) {
            Patient::where('patient_id',$data['patient_id'])->where('account_user_id',$request->user()->user_id)->firstOrFail();
        }

        $thread=DB::transaction(function() use($request,$data){
            $thread=SupportThread::create([
                'user_id'=>$request->user()->user_id,
                'patient_id'=>$data['patient_id'] ?? null,
                'subject'=>$data['subject'],
                'category'=>$data['category'],
                'status'=>'OPEN',
            ]);
            SupportMessage::create([
                'support_thread_id'=>$thread->support_thread_id,
                'sender_user_id'=>$request->user()->user_id,
                'message'=>$data['message'],
            ]);
            return $thread;
        });

        $this->audit->record('support.created',$thread);
        return back()->with('success','Support request sent to Office Staff.');
    }

    public function replySupport(Request $request, SupportThread $thread)
    {
        abort_unless($thread->user_id===$request->user()->user_id,403);
        $data=$request->validate(['message'=>['required','string','max:5000']]);
        SupportMessage::create([
            'support_thread_id'=>$thread->support_thread_id,
            'sender_user_id'=>$request->user()->user_id,
            'message'=>$data['message'],
        ]);
        $thread->update(['status'=>'OPEN']);
        return back()->with('success','Reply sent.');
    }
}
