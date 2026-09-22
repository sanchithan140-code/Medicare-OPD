<?php

namespace App\Http\Controllers;

use App\Http\Requests\ConsultationRequest;
use App\Models\Appointment;
use App\Models\AppointmentStatusHistory;
use App\Models\Consultation;
use App\Models\DiagnosisCondition;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabTest;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\AuditService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorController extends Controller
{
    public function __construct(private readonly AuditService $audit, private readonly NotificationService $notifications) {}

    private function doctor(Request $request): Doctor
    {
        return Doctor::with('user','specialty')
            ->where('user_id',$request->user()->user_id)
            ->firstOrFail();
    }

    public function dashboard(Request $request)
    {
        $doctor=$this->doctor($request);
        $appointments=Appointment::with('patient','slot')
            ->where('doctor_id',$doctor->doctor_id)
            ->whereIn('status',['CONFIRMED','CHECKED_IN'])
            ->whereHas('slot',fn($q)=>$q->whereDate('slot_start',today()))
            ->orderBy('slot_id')->get();

        return view('doctor.dashboard',[
            'doctor'=>$doctor,
            'appointments'=>$appointments,
            'waiting'=>$appointments->where('status','CHECKED_IN')->count(),
            'completedToday'=>Appointment::where('doctor_id',$doctor->doctor_id)->where('status','COMPLETED')->whereDate('completed_at',today())->count(),
        ]);
    }

    public function appointments(Request $request)
    {
        $doctor=$this->doctor($request);
        $filters=$request->validate([
            'date'=>['nullable','date_format:Y-m-d'],
            'patient_id'=>['nullable','string','max:50'],
        ]);
        $patientCode=trim($filters['patient_id'] ?? '');
        return view('doctor.appointments',[
            'appointments'=>Appointment::with('patient','slot','consultation')
                ->where('doctor_id',$doctor->doctor_id)
                ->when($patientCode !== '',fn($q)=>$q->whereHas('patient',fn($patient)=>$patient->where('patient_code',$patientCode)))
                ->when($filters['date'] ?? null,fn($q,$date)=>$q->whereHas('slot',fn($slot)=>$slot->whereDate('slot_start',$date)))
                ->orderByDesc(\App\Models\AppointmentSlot::select('slot_start')->whereColumn('appointment_slots.slot_id','appointments.slot_id'))
                ->orderByDesc('appointment_id')->paginate(20)->withQueryString(),
        ]);
    }

    public function history(Request $request, int $patient)
    {
        abort_unless($request->user()->account_status === 'ACTIVE' && $this->doctor($request)->active,403);
        return $this->patientRecordView($request, \App\Models\Patient::findOrFail($patient));
    }

    public function patientRecords(Request $request)
    {
        abort_unless($request->user()->account_status === 'ACTIVE' && $this->doctor($request)->active,403);
        $data=$request->validate(['patient_id'=>['nullable','string','max:50']]);
        $id=trim($data['patient_id'] ?? '');
        $patient=null;
        if ($id!=='') {
            $patient=\App\Models\Patient::where('patient_code',$id)->first();
            if (!$patient && ctype_digit($id)) {
                $patient=\App\Models\Patient::find($id);
            }
        }
        return $this->patientRecordView($request,$patient,$id);
    }

    private function patientRecordView(Request $request, ?\App\Models\Patient $patient, string $search='')
    {
        if ($patient) {
            $this->audit->record('patient.records_viewed',$patient,request:$request);
        }
        return response()->view('doctor.history',[
            'patient'=>$patient,
            'search'=>$search ?: $patient?->patient_code,
            'searched'=>trim((string) $request->input('patient_id'))!=='',
            'consultations'=>$patient ? Consultation::with('doctor.user','conditions','prescriptions.items')
                ->where('patient_id',$patient->patient_id)->latest('consultation_id')->get() : collect(),
            'labOrders'=>$patient ? LabOrder::with('items.test','items.result.attachments')
                ->where('patient_id',$patient->patient_id)->latest('lab_order_id')->get() : collect(),
        ])->header('Cache-Control','private, no-store, max-age=0');
    }

    public function consultationForm(Request $request, Appointment $appointment)
    {
        $doctor=$this->doctor($request);
        abort_unless($appointment->doctor_id===$doctor->doctor_id,403);
        abort_unless(in_array($appointment->status,['CONFIRMED','CHECKED_IN'],true),422,'Office Staff must confirm the appointment before the consultation can be recorded.');
        abort_unless($appointment->isWithinConsultationDate(),422,'Consultations cannot be recorded after the appointment date has passed or when the appointment date is missing.');

        return view('doctor.consultation',[
            'doctor'=>$doctor,
            'appointment'=>$appointment->load('patient','slot','consultation'),
            'conditions'=>DiagnosisCondition::where('active',true)->orderBy('condition_name')->get(),
            'labTests'=>LabTest::where('active',true)->orderBy('test_name')->get(),
        ]);
    }

    public function storeConsultation(ConsultationRequest $request, Appointment $appointment)
    {
        $doctor=$this->doctor($request);
        abort_unless($appointment->doctor_id===$doctor->doctor_id,403);
        $data = $request->validated();

        [$consultation,$labOrder] = DB::transaction(function() use($request,$appointment,$doctor,$data){
            $locked = Appointment::where('appointment_id',$appointment->appointment_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($locked->status,['CONFIRMED','CHECKED_IN'],true),422,'Only a confirmed or checked-in appointment can be completed.');
            abort_unless($locked->isWithinConsultationDate(),422,'Consultations cannot be recorded after the appointment date has passed or when the appointment date is missing.');
            abort_if(Consultation::where('appointment_id',$locked->appointment_id)->exists(),422,'This appointment already has a completed consultation record.');

            $consultation=Consultation::create([
                'appointment_id'=>$locked->appointment_id,
                'patient_id'=>$locked->patient_id,
                'doctor_id'=>$doctor->doctor_id,
                'started_at'=>$locked->checked_in_at ?: now(),
                'completed_at'=>now(),
                'chief_complaint'=>$data['chief_complaint'] ?: null,
                'clinical_notes'=>$data['clinical_notes'],
                'diagnosis_text'=>$data['diagnosis_text'],
                'treatment_plan'=>$data['treatment_plan'],
                'patient_summary'=>$data['patient_summary'] ?: null,
                'follow_up_date'=>$data['follow_up_date'] ?: null,
            ]);

            foreach(array_values($data['condition_ids'] ?? []) as $i=>$conditionId){
                DB::table('consultation_conditions')->insert([
                    'consultation_id'=>$consultation->consultation_id,
                    'condition_id'=>$conditionId,
                    'is_primary'=>$i===0,
                    'finalized'=>1,
                    'created_at'=>now(),
                ]);
            }

            if(!empty($data['medicine_name'])){
                $rx=Prescription::create([
                    'consultation_id'=>$consultation->consultation_id,
                    'prescribed_by'=>$doctor->doctor_id,
                    'instructions'=>'Follow the doctor instructions and contact the hospital if symptoms worsen.',
                    'status'=>'ACTIVE',
                ]);
                PrescriptionItem::create([
                    'prescription_id'=>$rx->prescription_id,
                    'medicine_name'=>$data['medicine_name'],
                    'dosage'=>$data['dosage'],
                    'frequency'=>$data['frequency'],
                    'duration_days'=>$data['duration_days'],
                ]);
            }

            $labOrder = null;
            if(!empty($data['lab_test_ids'])){
                $labOrder=LabOrder::create([
                    'lab_order_no'=>'LAB-'.now()->format('ymd').'-'.strtoupper(substr(bin2hex(random_bytes(4)),0,6)),
                    'consultation_id'=>$consultation->consultation_id,
                    'patient_id'=>$locked->patient_id,
                    'ordered_by_doctor_id'=>$doctor->doctor_id,
                    'status'=>'PENDING',
                    'priority'=>'ROUTINE',
                    'ordered_at'=>now(),
                ]);
                $tests=LabTest::whereIn('lab_test_id',$data['lab_test_ids'])->where('active',1)->lockForUpdate()->get();
                abort_unless($tests->count()===count(array_unique($data['lab_test_ids'])),422,'A selected laboratory test is no longer available.');
                foreach($tests as $test){
                    LabOrderItem::create([
                        'lab_order_id'=>$labOrder->lab_order_id,
                        'lab_test_id'=>$test->lab_test_id,
                        'status'=>'PENDING',
                        'fee'=>$test->standard_fee,
                    ]);
                }
            }

            if ($labOrder) { app(\App\Services\LabBillingService::class)->invoice($labOrder,$request->user()->user_id); }
            $old=$locked->status;
            $locked->update(['status'=>'COMPLETED','completed_at'=>now()]);
            AppointmentStatusHistory::create([
                'appointment_id'=>$locked->appointment_id,
                'from_status'=>$old,
                'to_status'=>'COMPLETED',
                'reason'=>'Consultation completed',
                'changed_by'=>$request->user()->user_id,
            ]);

            return [$consultation,$labOrder];
        }, 3);

        $appointment->refresh()->load('patient');
        $this->notifications->create(
            $appointment->patient->account_user_id,
            'consultation',
            'Consultation completed',
            "The consultation for appointment {$appointment->appointment_no} was completed. Your patient-visible summary and any laboratory requests are available in MEDICARE.",
            'Consultation',
            $consultation->consultation_id
        );

        if ($labOrder) {
            User::where('account_status','ACTIVE')
                ->whereHas('role',fn($query)=>$query->where('role_name','lab_officer'))
                ->pluck('user_id')
                ->each(fn($userId)=>$this->notifications->create(
                    (int) $userId,
                    'laboratory_order',
                    'New laboratory order',
                    "Laboratory order {$labOrder->lab_order_no} is waiting for processing.",
                    'LabOrder',
                    $labOrder->lab_order_id
                ));
        }

        $this->audit->record('consultation.saved',$consultation);

        return redirect()->route('doctor.appointments')->with('success','Consultation, diagnosis, prescription and laboratory requests were saved.');
    }

    public function schedule(Request $request)
    {
        $doctor=$this->doctor($request);
        return view('doctor.schedule',[
            'schedules'=>$doctor->schedules()->notEnded()->with('slots')->orderBy('schedule_date')->orderBy('start_time')->paginate(15),
            'leaves'=>DoctorLeave::where('doctor_id',$doctor->doctor_id)->latest('leave_id')->get(),
        ]);
    }

    public function requestLeave(Request $request)
    {
        $doctor=$this->doctor($request);
        $data=$request->validate([
            'start_at'=>['required','date','after:now'],
            'end_at'=>['required','date','after:start_at'],
            'reason'=>['required','string','max:500'],
        ]);
        $leave=DoctorLeave::create([
            'doctor_id'=>$doctor->doctor_id,
            'start_at'=>$data['start_at'],
            'end_at'=>$data['end_at'],
            'reason'=>$data['reason'],
            'status'=>'REQUESTED',
            'requested_by'=>$request->user()->user_id,
        ]);
        $this->audit->record('doctor.leave_requested',$leave);
        return back()->with('success','Leave request submitted for administrator approval.');
    }
}
