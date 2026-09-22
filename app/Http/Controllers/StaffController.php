<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Http\Requests\StaffBookingRequest;
use App\Http\Requests\StaffPatientRequest;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Doctor;
use App\Models\DoctorSchedule;
use App\Models\InsuranceClaim;
use App\Models\InsuranceProvider;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\QueueEntry;
use App\Models\SupportMessage;
use App\Models\SupportThread;
use App\Models\User;
use App\Services\AppointmentBookingService;
use App\Services\AuditService;
use App\Services\NotificationService;
use App\Services\PatientCodeService;
use App\Services\HospitalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffController extends Controller
{
    public function __construct(
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
        private readonly PatientCodeService $codes,
        private readonly HospitalMailService $mail,
        private readonly AppointmentBookingService $booking,
    ) {}

    public function dashboard()
    {
        return view('staff.dashboard',[
            'pending'=>Appointment::where('status','BOOKED')->count(),
            'today'=>Appointment::whereHas('slot',fn($q)=>$q->whereDate('slot_start',today()))->count(),
            'unresolved'=>SupportThread::whereNotIn('status',['RESOLVED','CLOSED'])->count(),
            'paymentsToday'=>Payment::where('status','COMPLETED')->whereDate('paid_at',today())->sum('amount'),
            'appointments'=>Appointment::with('patient','doctor.user','slot')->latest('appointment_id')->limit(10)->get(),
        ]);
    }

    public function appointments(Request $request)
    {
        $filters=$request->validate([
            'doctor_suffix'=>['nullable','regex:/^[0-9]{3}$/'],
            'doctor_id'=>['nullable','integer','exists:doctors,doctor_id'],
            'date'=>['nullable','date_format:Y-m-d'],
            'status'=>['nullable','in:BOOKED,CONFIRMED,CHECKED_IN,COMPLETED,CANCELLED,RESCHEDULED,NO_SHOW'],
        ]);
        $sessions=DoctorSchedule::with('doctor.user','doctor.specialty')
            ->when($filters['doctor_id'] ?? null,fn($q,$id)=>$q->where('doctor_id',$id))
            ->whereHas('appointments',fn($q)=>$q->when($filters['status'] ?? null,fn($q,$status)=>$q->where('status',$status)))
            ->when($filters['doctor_suffix'] ?? null,fn($q,$suffix)=>$q->whereHas('doctor',fn($d)=>$d->where('registration_no','SLMC-900'.$suffix)))
            ->when($filters['date'] ?? null,fn($q,$date)=>$q->whereDate('schedule_date',$date))
            ->with(['appointments'=>fn($q)=>$q->with('patient.account','slot')->orderBy('slot_id')])
            ->orderByDesc('schedule_date')->orderBy('start_time')->orderBy('doctor_id')
            ->paginate(15)->withQueryString();
        $doctors=Doctor::with('user','specialty')->get()->sortBy(fn($doctor)=>$doctor->user->full_name);
        return view('staff.appointments',compact('sessions','doctors'));
    }

    public function confirmSession(Request $request, DoctorSchedule $schedule)
    {
        $confirmed=DB::transaction(function() use($request,$schedule){
            $appointments=Appointment::where('schedule_id',$schedule->schedule_id)
                ->whereIn('status',['BOOKED','RESCHEDULED'])->orderBy('appointment_id')->lockForUpdate()->get();
            foreach($appointments as $appointment){
                $old=$appointment->status;
                $appointment->update(['status'=>'CONFIRMED','confirmed_at'=>now()]);
                AppointmentStatusHistory::create([
                    'appointment_id'=>$appointment->appointment_id,'from_status'=>$old,'to_status'=>'CONFIRMED',
                    'reason'=>'Session confirmed by hospital staff','changed_by'=>$request->user()->user_id,
                ]);
                $this->audit->record('appointment.status_changed',$appointment,[],['status'=>'CONFIRMED']);
            }
            return $appointments;
        });
        foreach($confirmed as $appointment){
            $appointment->load('patient.account');
            $this->notifications->create($appointment->patient->account_user_id,'appointment','Appointment confirmed',
                "Appointment {$appointment->appointment_no} is now CONFIRMED.",'Appointment',$appointment->appointment_id);
            $this->mail->appointmentStatus($appointment,'Your appointment status is now CONFIRMED.');
        }
        return back()->with('success',$confirmed->count().' appointment(s) confirmed for this session.');
    }
    public function schedules()
    {
        return view('staff.schedules', [
            'doctors' => Doctor::with('user')->where('active',true)->orderBy('doctor_id')->get(),
            'schedules' => DoctorSchedule::with('doctor.user','slots')
                ->latest('schedule_date')->latest('start_time')->paginate(20),
        ]);
    }

    public function createSchedule(Request $request)
    {
        $data=$request->validate([
            'doctor_id'=>['required','exists:doctors,doctor_id'],
            'schedule_date'=>['required','date','after_or_equal:today'],
            'start_time'=>['required','date_format:H:i'],
            'end_time'=>['required','date_format:H:i','after:start_time'],
            'slot_minutes'=>['required','integer','min:5','max:60'],
            'capacity'=>['required','integer','min:1','max:100'],
            'room_no'=>['nullable','string','max:30'],
        ]);

        $schedule=app(\App\Services\ScheduleCreationService::class)->create($data,$request->user()->user_id);
        $this->audit->record('schedule.created',$schedule);
        return back()->with('success','Doctor schedule and appointment slots generated.');
    }

    public function changeStatus(Request $request, Appointment $appointment)
    {
        $data=$request->validate([
            'status'=>['required','in:CONFIRMED,CHECKED_IN,NO_SHOW,CANCELLED'],
            'reason'=>['nullable','required_if:status,NO_SHOW,CANCELLED','string','max:500'],
        ]);

        $allowedTransitions = [
            'BOOKED' => ['CONFIRMED','CANCELLED','NO_SHOW'],
            'RESCHEDULED' => ['CONFIRMED','CANCELLED','NO_SHOW'],
            'CONFIRMED' => ['CHECKED_IN','CANCELLED','NO_SHOW'],
        ];
        abort_unless(in_array($data['status'], $allowedTransitions[$appointment->status] ?? [], true), 422, 'That status change is not allowed from the current appointment state.');
        $appointment->loadMissing('slot');
        if ($data['status'] === 'CHECKED_IN') {
            abort_unless($appointment->slot?->slot_start?->isSameDay(today()), 422, 'Patient check-in is available only on the appointment date.');
        }
        if ($data['status'] === 'NO_SHOW') {
            abort_unless($appointment->slot?->slot_start?->lte(now()), 422, 'An appointment cannot be marked as missed before its scheduled time.');
        }

        DB::transaction(function() use($request,$appointment,$data,$allowedTransitions){
            $locked=Appointment::whereKey($appointment->appointment_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($data['status'],$allowedTransitions[$locked->status] ?? [],true),422,'This appointment has already changed. Refresh the page.');
            $appointment->setRawAttributes($locked->getAttributes(),true);
            $old=$appointment->status;
            $changes=['status'=>$data['status']];
            if($data['status']==='CONFIRMED') $changes['confirmed_at']=now();
            if($data['status']==='CHECKED_IN') $changes['checked_in_at']=now();
            if($data['status']==='CANCELLED') {
                $changes['cancelled_at']=now();
                $changes['cancellation_reason']=$data['reason'] ?: 'Cancelled by hospital staff';
                $appointment->slot()->update(['slot_status'=>'AVAILABLE']);
                $this->booking->skipPendingReminders($appointment, 'Appointment cancelled by hospital staff');
                app(\App\Services\AppointmentInvoiceService::class)->cancelUnpaid($appointment);
            }
            if($data['status']==='NO_SHOW') {
                $this->booking->skipPendingReminders($appointment, 'Appointment marked as missed');
            }
            $appointment->update($changes);

            AppointmentStatusHistory::create([
                'appointment_id'=>$appointment->appointment_id,
                'from_status'=>$old,
                'to_status'=>$data['status'],
                'reason'=>$data['reason'] ?? null,
                'changed_by'=>$request->user()->user_id,
            ]);

            if($data['status']==='CHECKED_IN'){
                $next=(int)QueueEntry::where('schedule_id',$appointment->schedule_id)->max('queue_number')+1;
                QueueEntry::firstOrCreate(
                    ['appointment_id'=>$appointment->appointment_id],
                    [
                        'schedule_id'=>$appointment->schedule_id,
                        'queue_number'=>$next,
                        'priority_level'=>'NORMAL',
                        'queue_status'=>'WAITING',
                        'checked_in_at'=>now(),
                    ]
                );
            }
        });

        $appointment->load('patient.account');
        $this->notifications->create(
            $appointment->patient->account_user_id,
            'appointment',
            'Appointment status updated',
            "Appointment {$appointment->appointment_no} is now {$data['status']}.",
            'Appointment',
            $appointment->appointment_id
        );
        $this->audit->record('appointment.status_changed',$appointment,[],['status'=>$data['status']]);
        $this->mail->appointmentStatus($appointment, "Your appointment status is now {$data['status']}.");

        return back()->with('success','Appointment status updated.');
    }

    public function patients(Request $request)
    {
        $patients=Patient::with('account')
            ->when($request->filled('q'),function($q) use($request){
                $term='%'.$request->q.'%';
                $q->where(function($sub) use($term){
                    $sub->where('patient_code','like',$term)
                        ->orWhere('first_name','like',$term)
                        ->orWhere('last_name','like',$term)
                        ->orWhere('phone','like',$term);
                });
            })->latest('patient_id')->paginate(20)->withQueryString();

        return view('staff.patients',compact('patients'));
    }

    public function storePatient(StaffPatientRequest $request)
    {
        $data = $request->validated();

        $account=User::where('email',mb_strtolower($data['account_email']))->whereHas('role',fn($q)=>$q->where('role_name','patient'))->firstOrFail();
        $patient=Patient::create([
            'account_user_id'=>$account->user_id,
            'patient_code'=>$this->codes->generate(),
            'relationship_to_account'=>$data['relationship_to_account'],
            'first_name'=>$data['first_name'],
            'last_name'=>$data['last_name'],
            'dob'=>$data['dob'] ?: null,
            'sex'=>$data['sex'],
            'phone'=>$data['phone'] ?: null,
            'created_by'=>$request->user()->user_id,
        ]);
        $this->audit->record('staff.patient_created',$patient);
        return back()->with('success',"Patient {$patient->patient_code} created.");
    }

    public function bookingForm(Request $request)
    {
        $date = $request->query('date', now()->addDay()->toDateString());
        $doctorId = $request->integer('doctor_id') ?: null;

        return view('staff.book', [
            'patients' => Patient::with('account')->where('active',true)->orderBy('first_name')->get(),
            'doctors' => \App\Models\Doctor::with('user','specialty')->where('active',true)->orderBy('doctor_id')->get(),
            'slots' => $this->booking->availableSlots($doctorId, $date),
            'date' => $date,
            'doctorId' => $doctorId,
        ]);
    }

    public function storeBooking(StaffBookingRequest $request)
    {
        $data = $request->validated();
        $patient = Patient::where('patient_id',$data['patient_id'])->where('active',true)->firstOrFail();
        $appointment = $this->booking->book($patient, (int) $data['slot_id'], $request->user()->user_id, 'STAFF');
        if (! empty($data['booking_notes'])) {
            $appointment->update(['booking_notes' => $data['booking_notes']]);
        }

        return redirect()->route('staff.appointments')->with('success', "Appointment {$appointment->appointment_no} booked on behalf of the patient.");
    }

    public function billing(Request $request)
    {
        $search = trim((string)$request->query('q'));
        return view('staff.billing',[
            'invoices'=>Invoice::with('patient','appointment.doctor.user','appointment.slot','items','payments','labOrder')
                ->when($search,fn($query)=>$query->whereHas('patient',fn($patient)=>$patient
                    ->where('patient_code','like',"%{$search}%")
                    ->orWhere('first_name','like',"%{$search}%")
                    ->orWhere('last_name','like',"%{$search}%")))
                ->latest('invoice_id')->paginate(20)->withQueryString(),
            'patients'=>Patient::where('active',true)->orderBy('first_name')->get(),
            'search'=>$search,
        ]);
    }

    public function createInvoice(Request $request)
    {
        $data=$request->validate([
            'patient_id'=>['required','exists:patients,patient_id'],
            'appointment_id'=>['nullable','exists:appointments,appointment_id'],
            'description'=>['required','string','max:255'],
            'item_type'=>['required','in:CONSULTATION,LABORATORY,PHARMACY,OTHER'],
            'amount'=>['required','numeric','min:0.01','max:99999999.99'],
        ]);
        if (! empty($data['appointment_id'])) {
            $validAppointment = Appointment::where('appointment_id',$data['appointment_id'])
                ->where('patient_id',$data['patient_id'])->exists();
            abort_unless($validAppointment,422,'The selected appointment does not belong to the selected patient.');
        }

        $invoice=DB::transaction(function() use($request,$data){
            $invoice=Invoice::create([
                'invoice_no'=>'INV-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),
                'patient_id'=>$data['patient_id'],
                'appointment_id'=>$data['appointment_id'] ?: null,
                'subtotal'=>$data['amount'],
                'total_amount'=>$data['amount'],
                'balance_amount'=>$data['amount'],
                'status'=>'ISSUED',
                'issued_at'=>now(),
                'created_by'=>$request->user()->user_id,
            ]);
            InvoiceItem::create([
                'invoice_id'=>$invoice->invoice_id,
                'item_type'=>$data['item_type'],
                'description'=>$data['description'],
                'quantity'=>1,
                'unit_price'=>$data['amount'],
                'line_total'=>$data['amount'],
            ]);
            return $invoice;
        });

        $invoice->load('patient');
        $this->notifications->create(
            $invoice->patient->account_user_id,
            'invoice',
            'New invoice issued',
            "Invoice {$invoice->invoice_no} was issued for LKR ".number_format((float) $invoice->total_amount, 2).'.',
            'Invoice',
            $invoice->invoice_id
        );
        $this->audit->record('invoice.created',$invoice);
        return back()->with('success',"Invoice {$invoice->invoice_no} created.");
    }

    public function recordPayment(PaymentRequest $request, Invoice $invoice)
    {
        $data = $request->validated();

        $payment = DB::transaction(function() use($request,$invoice,$data){
            $locked=Invoice::where('invoice_id',$invoice->invoice_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status,['ISSUED','PARTIALLY_PAID']),422,'This invoice is already paid or closed.');
            abort_if((float)$data['amount']>(float)$locked->balance_amount,422,'Payment exceeds the remaining balance.');
            $payment = Payment::create([
                'payment_no'=>'PAY-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),
                'invoice_id'=>$locked->invoice_id,
                'payment_method'=>$data['payment_method'],
                'amount'=>$data['amount'],
                'transaction_reference'=>$data['transaction_reference'] ?? null,
                'status'=>'COMPLETED',
                'received_by'=>$request->user()->user_id,
                'paid_at'=>now(),
            ]);
            $paid=(float)$locked->paid_amount+(float)$data['amount'];
            $balance=max(0,(float)$locked->total_amount-$paid-(float)$locked->insurance_covered);
            $locked->update([
                'paid_amount'=>$paid,
                'balance_amount'=>$balance,
                'status'=>$balance<=0?'PAID':'PARTIALLY_PAID',
            ]);
            return $payment;
        });

        $invoice->load('patient.account');
        if ($invoice->patient?->account) {
            $this->mail->paymentReceipt($invoice->patient->account, $invoice->invoice_no, $payment->payment_no, (float) $payment->amount, $payment->payment_method);
            $this->notifications->create($invoice->patient->account_user_id, 'payment', 'Payment recorded', "Payment {$payment->payment_no} was recorded for invoice {$invoice->invoice_no}.", 'Payment', $payment->payment_id);
        }
        $this->audit->record('payment.recorded',$invoice,[],['amount'=>$data['amount'],'method'=>$data['payment_method']]);
        return back()->with('success','Payment recorded and invoice balance updated.');
    }

    public function confirmHospitalRefund(Request $request, Invoice $invoice, Payment $payment)
    {
        $request->validate(['confirm_refund' => ['required', 'accepted']]);
        $changed = DB::transaction(function () use ($request, $invoice, $payment) {
            $locked = Invoice::whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            $refund = Payment::whereKey($payment->payment_id)->where('invoice_id', $locked->invoice_id)->lockForUpdate()->firstOrFail();
            if ($refund->status === 'REFUNDED') {
                return false;
            }
            abort_unless($locked->status === 'VOID' && $refund->status === 'COMPLETED'
                && $refund->payment_method !== 'INSURANCE'
                && str_starts_with((string) $refund->notes, 'Hospital refund awaiting collection; '),
                422, 'This payment is not awaiting a hospital refund.');
            $refund->update([
                'status' => 'REFUNDED',
                'notes' => mb_substr('Hospital refund collected on '.now()->toDateTimeString().' by staff #'.$request->user()->user_id.'; '.substr($refund->notes, strlen('Hospital refund awaiting collection; ')), 0, 500),
            ]);
            $locked->update(['paid_amount' => max(0, (float) $locked->paid_amount - (float) $refund->amount)]);
            $this->audit->record('payment.hospital_refund_collected', $refund, ['status' => 'COMPLETED'], [
                'status' => 'REFUNDED', 'amount' => $refund->amount, 'invoice_id' => $locked->invoice_id,
            ]);
            return true;
        });
        return back()->with('success', $changed ? 'Hospital refund handed over and payment records updated.' : 'This refund was already recorded.');
    }

    public function insurance()
    {
        return view('staff.insurance',[
            'claims'=>InsuranceClaim::with('provider','invoice.patient')->latest('claim_id')->paginate(20),
            'providers'=>InsuranceProvider::where('active',true)->get(),
            'invoices'=>Invoice::with('patient')->whereIn('status',['ISSUED','PARTIALLY_PAID'])->get(),
        ]);
    }

    public function createClaim(Request $request)
    {
        $data=$request->validate([
            'invoice_id'=>['required','exists:invoices,invoice_id'],
            'provider_id'=>['required','exists:insurance_providers,provider_id'],
            'policy_no'=>['required','string','max:100'],
            'requested_amount'=>['required','numeric','min:0.01'],
        ]);
        $invoice=Invoice::findOrFail($data['invoice_id']);
        abort_if((float)$data['requested_amount']>(float)$invoice->balance_amount,422,'The requested insurance amount exceeds the invoice balance.');
        abort_if(InsuranceClaim::where('invoice_id',$invoice->invoice_id)->whereNotIn('status',['REJECTED','CANCELLED'])->exists(),422,'This invoice already has an active insurance claim.');
        $claim=InsuranceClaim::create([
            'claim_no'=>'CLM-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),
            'invoice_id'=>$invoice->invoice_id,
            'patient_id'=>$invoice->patient_id,
            'provider_id'=>$data['provider_id'],
            'policy_no'=>$data['policy_no'],
            'requested_amount'=>$data['requested_amount'],
            'status'=>'SUBMITTED',
            'submitted_at'=>now(),
            'processed_by'=>$request->user()->user_id,
        ]);
        $this->notifications->create(
            $invoice->patient->account_user_id,
            'insurance',
            'Insurance claim submitted',
            "Claim {$claim->claim_no} was submitted for processing.",
            'InsuranceClaim',
            $claim->claim_id
        );
        $this->audit->record('insurance.claim_created',$claim);
        return back()->with('success','Insurance claim submitted.');
    }

    public function updateClaim(Request $request, InsuranceClaim $claim)
    {
        $data=$request->validate([
            'status'=>['required','in:PENDING,SUBMITTED,MORE_INFO,APPROVED,REJECTED,CANCELLED'],
            'approved_amount'=>['nullable','required_if:status,APPROVED','numeric','min:0.01'],
            'notes'=>['nullable','string','max:500'],
        ]);
        abort_if(in_array($claim->status,['APPROVED','REJECTED','CANCELLED'],true),422,'A finalized claim cannot be changed.');
        if ($data['status'] === 'APPROVED') {
            abort_if((float)$data['approved_amount']>(float)$claim->requested_amount,422,'The approved amount cannot exceed the requested amount.');
        }

        $payment = DB::transaction(function() use($request,$claim,$data){
            $lockedClaim=InsuranceClaim::where('claim_id',$claim->claim_id)->lockForUpdate()->firstOrFail();
            $invoice=Invoice::where('invoice_id',$lockedClaim->invoice_id)->lockForUpdate()->firstOrFail();
            abort_if(in_array($lockedClaim->status,['APPROVED','REJECTED','CANCELLED'],true),422,'A finalized claim cannot be changed.');

            $approved = $data['status']==='APPROVED' ? (float)$data['approved_amount'] : (float)$lockedClaim->approved_amount;
            $lockedClaim->update([
                'status'=>$data['status'],
                'approved_amount'=>$approved,
                'notes'=>$data['notes'] ?? $lockedClaim->notes,
                'decided_at'=>in_array($data['status'],['APPROVED','REJECTED'],true)?now():null,
                'processed_by'=>$request->user()->user_id,
            ]);

            if($data['status']!=='APPROVED') return null;

            $covered=min($approved,(float)$invoice->balance_amount);
            if ($covered <= 0) return null;
            $newInsurance=(float)$invoice->insurance_covered+$covered;
            $balance=max(0,(float)$invoice->total_amount-(float)$invoice->paid_amount-$newInsurance);
            $invoice->update([
                'insurance_covered'=>$newInsurance,
                'balance_amount'=>$balance,
                'status'=>$balance<=0?'PAID':'PARTIALLY_PAID',
            ]);

            return Payment::create([
                'payment_no'=>'PAY-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),
                'invoice_id'=>$invoice->invoice_id,
                'payment_method'=>'INSURANCE',
                'amount'=>$covered,
                'transaction_reference'=>$lockedClaim->claim_no,
                'status'=>'COMPLETED',
                'received_by'=>$request->user()->user_id,
                'paid_at'=>now(),
                'notes'=>'Insurance claim approval',
            ]);
        },3);

        $claim->refresh()->load('patient.account','invoice');
        $this->notifications->create(
            $claim->patient->account_user_id,
            'insurance',
            'Insurance claim updated',
            "Claim {$claim->claim_no} is now {$data['status']}.",
            'InsuranceClaim',
            $claim->claim_id
        );
        if ($payment && $claim->patient?->account) {
            $this->mail->paymentReceipt($claim->patient->account, $claim->invoice->invoice_no, $payment->payment_no, (float)$payment->amount, 'INSURANCE');
        }
        $this->audit->record('insurance.claim_updated',$claim,[],['status'=>$data['status']]);
        return back()->with('success','Insurance claim updated and the invoice balance was reconciled.');
    }

    public function support()
    {
        return view('staff.support',[
            'threads'=>SupportThread::with('messages.sender')->latest('updated_at')->paginate(20),
        ]);
    }

    public function replySupport(Request $request, SupportThread $thread)
    {
        $data=$request->validate([
            'message'=>['required','string','max:5000'],
            'status'=>['required','in:OPEN,IN_PROGRESS,RESOLVED,CLOSED'],
        ]);
        SupportMessage::create([
            'support_thread_id'=>$thread->support_thread_id,
            'sender_user_id'=>$request->user()->user_id,
            'message'=>$data['message'],
        ]);
        $thread->update(['status'=>$data['status'],'assigned_to'=>$request->user()->user_id]);
        $this->notifications->create($thread->user_id,'support','Support reply received','A hospital staff member replied to your support request. Sign in to read the conversation.','SupportThread',$thread->support_thread_id);
        $thread->load('user');
        if ($thread->user) { $this->mail->supportReply($thread->user, $thread->subject); }
        return back()->with('success','Support reply sent.');
    }
}
