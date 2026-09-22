<?php
namespace App\Http\Controllers;

use App\Models\{User,Role,Patient,Doctor,Invoice,InvoiceItem,Payment,AppointmentStatusHistory};
use App\Services\{AppointmentBookingService,PatientCodeService,AuditService,AccountPatientService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB,Hash};

class WalkInController extends Controller
{
    public function index(Request $request, AppointmentBookingService $booking)
    {
        $data=$request->validate(['q'=>['nullable','string','max:100'],'doctor_id'=>['nullable','integer','exists:doctors,doctor_id'],'date'=>['nullable','date_format:Y-m-d']]);
        $q=trim($data['q'] ?? '');
        return view('staff.walk-in',[
            'patients'=>$q==='' ? collect() : Patient::where('active',1)->where(function($query)use($q){
                $query->where('patient_code',$q)->orWhere('phone',$q)->orWhere('nic_passport',$q)
                    ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?",['%'.addcslashes($q,'%_\\').'%']);
            })->limit(30)->get(),
            'doctors'=>Doctor::with('user','specialty')->where('active',1)->get(),
            'slots'=>!empty($data['doctor_id']) ? $booking->availableSlots((int)$data['doctor_id'],$data['date'] ?? today()->toDateString()) : collect(),
        ]);
    }

    public function store(Request $request, AppointmentBookingService $booking, PatientCodeService $codes, AuditService $audit)
    {
        $data=$request->validate([
            'patient_id'=>['nullable','integer','exists:patients,patient_id'],
            'first_name'=>['required_without:patient_id','nullable','string','max:70'],
            'last_name'=>['nullable','string','max:70'],
            'dob'=>['nullable','date','before_or_equal:today'],
            'sex'=>['required_without:patient_id','nullable','in:MALE,FEMALE,OTHER,UNKNOWN'],
            'phone'=>['nullable',new \App\Rules\TenDigitPhone],
            'nic_passport'=>['nullable','string','max:30'],
            'slot_id'=>['required','integer','exists:appointment_slots,slot_id'],
            'payment_method'=>['required','in:CASH,CARD'],
            'transaction_reference'=>['nullable','string','max:100'],
            'payment_received'=>['accepted'],
        ]);
        $invoice=DB::transaction(function()use($request,$data,$booking,$codes,$audit){
            if(!empty($data['patient_id'])) {
                $patient=Patient::whereKey($data['patient_id'])->where('active',1)->lockForUpdate()->firstOrFail();
            } else {
                if(!empty($data['nic_passport'])) {
                    abort_if(Patient::where('nic_passport',$data['nic_passport'])->exists(),422,'A patient already has this NIC/passport. Search and select the existing record.');
                }
                $owner=User::create([
                    'role_id'=>Role::where('role_name','patient')->value('role_id'),
                    'full_name'=>trim($data['first_name'].' '.($data['last_name'] ?? '')),
                    'email'=>'walkin-'.bin2hex(random_bytes(16)).'@walkin.invalid',
                    'password_hash'=>Hash::make(bin2hex(random_bytes(32))),
                    'account_status'=>'DISABLED','is_walk_in'=>1,
                ]);
                $patient=Patient::create([
                    'account_user_id'=>$owner->user_id,'patient_code'=>$codes->generate(),
                    'first_name'=>$data['first_name'],'last_name'=>$data['last_name'] ?? '',
                    'dob'=>$data['dob'] ?? null,'sex'=>$data['sex'],'phone'=>$data['phone'] ?? null,
                    'nic_passport'=>$data['nic_passport'] ?? null,'relationship_to_account'=>'Self',
                    'active'=>1,'created_by'=>$request->user()->user_id,
                ]);
            }
            $appointment=$booking->book($patient,(int)$data['slot_id'],$request->user()->user_id,'STAFF');
            $appointment->update(['status'=>'CONFIRMED','confirmed_at'=>now()]);
            AppointmentStatusHistory::create(['appointment_id'=>$appointment->appointment_id,'from_status'=>'BOOKED','to_status'=>'CONFIRMED','reason'=>'Walk-in payment received at hospital','changed_by'=>$request->user()->user_id]);
            $amount=$appointment->consultation_fee;
            $invoice=Invoice::create([
                'invoice_no'=>'INV-'.strtoupper(bin2hex(random_bytes(8))),
                'patient_id'=>$patient->patient_id,'appointment_id'=>$appointment->appointment_id,
                'subtotal'=>$amount,'total_amount'=>$amount,'paid_amount'=>$amount,'balance_amount'=>0,
                'status'=>'PAID','issued_at'=>now(),'created_by'=>$request->user()->user_id,
            ]);
            InvoiceItem::create(['invoice_id'=>$invoice->invoice_id,'item_type'=>'CONSULTATION','description'=>'OPD consultation','quantity'=>1,'unit_price'=>$amount,'line_total'=>$amount]);
            Payment::create([
                'invoice_id'=>$invoice->invoice_id,'payment_no'=>'PAY-'.strtoupper(bin2hex(random_bytes(8))),
                'payment_method'=>$data['payment_method'],'amount'=>$amount,'status'=>'COMPLETED',
                'received_by'=>$request->user()->user_id,'paid_at'=>now(),
                'transaction_reference'=>$data['transaction_reference'] ?? null,
                'notes'=>$data['payment_method']==='CARD' ? 'Paid at hospital card terminal' : 'Cash received at reception',
            ]);
            $audit->record('walk_in.paid',$invoice);
            return $invoice;
        });
        return redirect()->route('staff.receipt',$invoice);
    }

    public function receipt(Invoice $invoice)
    {
        /** @var \App\Models\User $user */
        $user = \Illuminate\Support\Facades\Auth::user();
        abort_if($user->isRole('lab_officer') && !$invoice->lab_order_id,403);
        $invoice->load('patient','appointment.doctor.user','appointment.slot','appointment.schedule','payments','labOrder.items.test');
        return response()->view('staff.receipt',compact('invoice'))->header('Cache-Control','private, no-store');
    }

    public function receipts(Request $request)
    {
        $data=$request->validate(['q'=>['nullable','string','max:100'],'date'=>['nullable','date_format:Y-m-d']]);
        $search=trim($data['q'] ?? '');
        $invoices=Invoice::with('patient','appointment.doctor.user','payments')
            ->whereIn('appointment_id',AppointmentStatusHistory::select('appointment_id')
                ->where('reason','Walk-in payment received at hospital'))
            ->when($search !== '',function($query)use($search){
                $query->where(function($q)use($search){
                    $q->where('invoice_no',$search)->orWhereHas('patient',fn($p)=>$p
                        ->where('patient_code',$search)
                        ->orWhereRaw("CONCAT(first_name,' ',last_name) LIKE ?",['%'.addcslashes($search,'%_\\').'%']));
                });
            })
            ->when($data['date'] ?? null,fn($q,$date)=>$q->whereDate('issued_at',$date))
            ->latest('invoice_id')->paginate(20)->withQueryString();
        return response()->view('staff.walk-in-receipts',compact('invoices'))->header('Cache-Control','private, no-store');
    }

    public function links()
    {
        $requests=User::whereNotNull('requested_patient_code')->where('account_status','ACTIVE')->whereNotNull('email_verified_at')->get();
        return view('staff.patient-links',[
            'requests'=>$requests,
            'records'=>Patient::whereIn('patient_code',$requests->pluck('requested_patient_code'))->get()->keyBy('patient_code'),
        ]);
    }

    public function link(Request $request, User $user, AuditService $audit)
    {
        $data=$request->validate(['patient_code'=>['required','string','size:4'],'identity_verified'=>['accepted'],'verification_note'=>['required','string','min:10','max:500']]);
        $patient=DB::transaction(function()use($user,$data,$audit){
            $account=User::whereKey($user->user_id)->lockForUpdate()->firstOrFail();
            abort_unless($account->isRole('patient') && $account->email_verified_at && $account->requested_patient_code!==null,422,'No pending verified account request.');
            abort_if($account->patients()->exists(),422,'This account already has patient records.');
            $patient=Patient::where('patient_code',$data['patient_code'])->lockForUpdate()->firstOrFail();
            $oldOwner=User::whereKey($patient->account_user_id)->lockForUpdate()->firstOrFail();
            abort_unless($oldOwner->is_walk_in && $oldOwner->account_status==='DISABLED',422,'This record is already linked to an online account.');
            $patient->update(['account_user_id'=>$account->user_id,'email'=>$account->email]);
            $account->update(['requested_patient_code'=>null]);
            $audit->record('patient.account_linked',$patient,['account_user_id'=>$oldOwner->user_id],['account_user_id'=>$account->user_id,'verification_note'=>$data['verification_note']]);
            return $patient;
        });
        app(\App\Services\HospitalMailService::class)->welcome($user->fresh(),$patient);
        return back()->with('success','Patient record linked. The original patient ID and medical history are preserved.');
    }
}
