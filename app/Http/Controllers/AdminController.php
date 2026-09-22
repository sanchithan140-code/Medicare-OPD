<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminUserRequest;
use App\Models\AuditLog;
use App\Models\DiagnosisCondition;
use App\Models\Doctor;
use App\Models\DoctorLeave;
use App\Models\DoctorSchedule;
use App\Models\LabTest;
use App\Models\Role;
use App\Models\Specialty;
use App\Models\User;
use App\Models\WeeklyDiseaseTrend;
use App\Services\AuditService;
use App\Services\TrendService;
use App\Services\NotificationService;
use App\Services\HospitalMailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AdminController extends Controller
{
    public function __construct(private readonly AuditService $audit, private readonly TrendService $trends, private readonly NotificationService $notifications, private readonly HospitalMailService $mail) {}

    public function dashboard()
    {
        return view('admin.dashboard',[
            'users'=>User::count(),
            'doctors'=>Doctor::where('active',true)->count(),
            'appointments'=>\App\Models\Appointment::count(),
            'revenue'=>\App\Models\Payment::where('status','COMPLETED')->sum('amount'),
            'pendingLeaves'=>DoctorLeave::where('status','REQUESTED')->count(),
            'trends'=>WeeklyDiseaseTrend::with('condition')->latest('week_start')->orderByDesc('case_count')->limit(10)->get(),
        ]);
    }

    public function users(Request $request)
    {
        $roles = Role::whereIn('role_name', Role::CREATABLE_ROLES)
            ->orderBy('role_id')->get();

        return view('admin.users',[
            'users'=>User::with('role')->when($request->filled('role'),fn($q)=>$q->whereHas('role',fn($r)=>$r->where('role_name',$request->role)))->latest('user_id')->paginate(25)->withQueryString(),
            'roles'=>$roles,
        ]);
    }

    public function createUser(AdminUserRequest $request)
    {
        $data = $request->validated();
        $selectedRole=Role::findOrFail($data['role_id']);
        abort_unless(in_array($selectedRole->role_name, Role::CREATABLE_ROLES, true),422,'Only office staff and laboratory officers can be created here.');
        $user=User::create([
            'role_id'=>$data['role_id'],
            'full_name'=>$data['full_name'],
            'email'=>mb_strtolower($data['email']),
            'phone'=>$data['phone'] ?: null,
            'password_hash'=>Hash::make($data['password']),
            'account_status'=>'ACTIVE',
            'email_verified_at'=>now(),
        ]);
        $this->audit->record('admin.user_created',$user,[],['role_id'=>$data['role_id']]);
        return back()->with('success','Portal user created.');
    }

    public function toggleUser(Request $request, User $user)
    {
        abort_if($user->user_id===$request->user()->user_id,422,'You cannot disable your own account.');
        abort_if($user->role?->role_name==='main_admin',422,'The Main Administrator account cannot be disabled.');
        $new=$user->account_status==='ACTIVE'?'DISABLED':'ACTIVE';
        $user->update(['account_status'=>$new]);
        $this->audit->record('admin.user_status_changed',$user,[],['status'=>$new]);
        return back()->with('success',"User status changed to {$new}.");
    }

    public function doctors()
    {
        return view('admin.doctors',[
            'doctors'=>Doctor::with('user','specialty','schedules')->latest('doctor_id')->paginate(20),
            'specialties'=>Specialty::where('active',true)->get(),
            'leaves'=>DoctorLeave::with('doctor.user')->latest('leave_id')->limit(20)->get(),
        ]);
    }

    public function createDoctor(Request $request)
    {
        $request->merge([
            'full_name' => trim((string) $request->input('full_name')),
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);
        $data=$request->validate([
            'full_name'=>['required','string','min:3','max:150'],
            'email'=>['required','email:rfc','max:190','unique:users,email'],
            'password'=>['required','string',\Illuminate\Validation\Rules\Password::min(8)->letters()->mixedCase()->numbers()->symbols()],
            'specialty_id'=>['required','exists:specialties,specialty_id'],
            'registration_no'=>['nullable','string','max:80','unique:doctors,registration_no'],
            'qualifications_summary'=>['nullable','string','max:255'],
            'bio'=>['nullable','string','max:5000'],
            'consultation_fee'=>['required','numeric','min:0'],
        ]);
        $doctor = DB::transaction(function () use ($data) {
            $user = User::create([
                'role_id' => Role::where('role_name', 'doctor')->firstOrFail()->role_id,
                'full_name' => $data['full_name'],
                'email' => $data['email'],
                'password_hash' => Hash::make($data['password']),
                'account_status' => 'ACTIVE',
                'email_verified_at' => now(),
            ]);
            unset($data['full_name'], $data['email'], $data['password']);
            return Doctor::create($data + ['user_id' => $user->user_id, 'active' => true]);
        });
        $this->audit->record('admin.doctor_created',$doctor);
        return back()->with('success','Doctor account and profile created.');
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

    public function decideLeave(Request $request, DoctorLeave $leave)
    {
        $data=$request->validate(['status'=>['required','in:APPROVED,REJECTED']]);
        $affected=DB::transaction(function()use($request,$leave,$data){
            Doctor::whereKey($leave->doctor_id)->lockForUpdate()->firstOrFail();
            $locked=DoctorLeave::whereKey($leave->leave_id)->lockForUpdate()->firstOrFail();
            if($locked->status===$data['status'])return collect();
            if($locked->status!=='REQUESTED')throw ValidationException::withMessages(['status'=>'This leave request already has a final decision.']);
            $locked->update(['status'=>$data['status'],'approved_by'=>$request->user()->user_id,'approved_at'=>now()]);
            if($data['status']!=='APPROVED')return collect();
            DB::table('appointment_slots')->join('doctor_schedules','doctor_schedules.schedule_id','=','appointment_slots.schedule_id')
                ->where('doctor_schedules.doctor_id',$leave->doctor_id)
                ->where('appointment_slots.slot_start','<',$locked->end_at)->where('appointment_slots.slot_end','>',$locked->start_at)
                ->where('appointment_slots.slot_status','AVAILABLE')->update(['appointment_slots.slot_status'=>'BLOCKED']);
            return \App\Models\Appointment::with('patient.account','doctor.user','slot')
                ->where('doctor_id',$leave->doctor_id)->whereIn('status',['BOOKED','CONFIRMED','RESCHEDULED'])
                ->whereHas('slot',fn($q)=>$q->where('slot_start','<',$locked->end_at)->where('slot_end','>',$locked->start_at))->get();
        });
        foreach($affected as $appointment){
            $this->notifications->create($appointment->patient->account_user_id,'doctor_unavailable','Doctor unavailable',"Doctor unavailable for appointment {$appointment->appointment_no}. Select another date, ask staff for a same-specialty doctor, or cancel.",'Appointment',$appointment->appointment_id);
            $this->mail->doctorUnavailable($appointment);
        }
        $this->audit->record('admin.leave_decided',$leave,[],['status'=>$data['status']]);
        return back()->with('success','Doctor leave decision saved.');
    }
    public function catalogs()
    {
        return view('admin.catalogs',[
            'specialties'=>Specialty::orderBy('specialty_name')->get(),
            'labTests'=>LabTest::orderBy('test_name')->get(),
            'conditions'=>DiagnosisCondition::orderBy('condition_name')->get(),
        ]);
    }

    public function storeCatalog(Request $request)
    {
        $data=$request->validate([
            'catalog'=>['required','in:specialty,lab_test,condition'],
            'name'=>['required','string','max:150'],
            'code'=>['nullable','string','max:30'],
            'fee'=>['nullable','numeric','min:0'],
            'description'=>['nullable','string','max:1000'],
        ]);
        match($data['catalog']){
            'specialty'=>Specialty::firstOrCreate(['specialty_name'=>$data['name']],['description'=>$data['description'] ?? null,'active'=>true]),
            'lab_test'=>LabTest::firstOrCreate(['test_name'=>$data['name']],['test_code'=>$data['code'] ?? strtoupper(substr(preg_replace('/\W+/','',$data['name']),0,8)),'standard_fee'=>$data['fee'] ?? 0,'active'=>true]),
            'condition'=>DiagnosisCondition::firstOrCreate(['condition_name'=>$data['name']],['icd_code'=>$data['code'] ?? null,'category'=>$data['description'] ?? null,'active'=>true]),
        };
        return back()->with('success','Catalog item saved.');
    }

    public function analytics()
    {
        return view('admin.analytics',[
            'appointmentByStatus'=>DB::table('appointments')->selectRaw('status,COUNT(*) total')->groupBy('status')->get(),
            'paymentByMethod'=>DB::table('payments')->where('status','COMPLETED')->selectRaw('payment_method,SUM(amount) total')->groupBy('payment_method')->get(),
            'totalRevenue'=>\App\Models\Payment::where('status','COMPLETED')->sum('amount'),
            'payments'=>\App\Models\Payment::with('invoice.patient','invoice.appointment.doctor.user','invoice.labOrder')->where('status','COMPLETED')->latest('paid_at')->limit(100)->get(),
            'trends'=>WeeklyDiseaseTrend::with('condition')->latest('week_start')->paginate(25),
        ]);
    }

    public function generateTrends()
    {
        $count=$this->trends->generate();
        return back()->with('success',"Weekly disease trends generated for {$count} condition(s).");
    }

    public function audit()
    {
        return view('admin.audit',['logs'=>AuditLog::latest('audit_id')->paginate(30)]);
    }
}
