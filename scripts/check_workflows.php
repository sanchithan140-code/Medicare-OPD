<?php
// Functional regression tests. Database fixtures are rolled back and outgoing requests are blocked.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB,Mail,Http,Auth,Hash,Validator,Storage};
use App\Models\{User,Role,Patient,Doctor,DoctorSchedule,AppointmentSlot,Appointment,Invoice,Payment,LabOrder,LabTest,DoctorLeave};
use App\Http\Controllers\{AuthController,StaffController,PatientController,AdminController,LabBookingController,LabController};
use App\Services\{AppointmentBookingService,LabBillingService,AuditService};
use Illuminate\Http\Request;
config(['session.driver'=>'array','cache.default'=>'array']);
Mail::fake();Http::preventStrayRequests();
class WorkflowAiGateway extends App\Services\AI\AiGateway {
    public bool $failLab=false;
    public function __construct() {}
    public function explainNoShowRisk(array $features):array{return ['risk_label'=>'LOW'];}
    public function diseaseTrendCommentary(array $rows):array{return ['summary'=>'Workflow trend summary'];}
    public function summarizeLabResult(string $name,string $text,?array $attachment=null):array{if($this->failLab)throw new RuntimeException('Simulated AI timeout');return ['summary'=>'TEST_DOCTOR_ONLY_SUMMARY','abnormal_flag'=>false,'_provider'=>'local','_model'=>'test'];}
}
$app->instance(App\Services\AI\AiGateway::class,new WorkflowAiGateway);
$mail=new class extends App\Services\HospitalMailService {
    public bool $failOtp=false;
    public string $code='';public ?string $welcomeId=null;
    public function otp(User $user,string $code,string $purpose):bool{$this->code=$code;return !$this->failOtp;}
    public function welcome(User $user,Patient $patient):bool{$this->welcomeId=$patient->patient_code;return true;}
};
$app->instance(App\Services\HospitalMailService::class,$mail);
view()->share('errors',new Illuminate\Support\ViewErrorBag());
$session=app('session')->driver();$session->start();
$check=function($ok,$message){if(!$ok)throw new RuntimeException($message);};
$blocked=function(callable $fn,int $code=422)use($check){
    try{$fn();}catch(Illuminate\Validation\ValidationException $e){$check($code===422,'Unexpected validation');return;}
    catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$check($e->getStatusCode()===$code,'Wrong rejection status');return;}
    throw new RuntimeException('Unsafe action was accepted');
};
$request=function(User $user,array $data=[],string $class=Request::class,array $files=[])use($app,$session){
    Auth::guard()->setUser($user);
    $r=$class::create('/test','POST',$data,[], $files);
    $r->setUserResolver(fn()=>$user);$r->setLaravelSession($session);$app->instance('request',$r);
    if($r instanceof Illuminate\Foundation\Http\FormRequest){
        $r->setContainer($app);$r->setRedirector(app('redirect'));
        $r->setValidator(Validator::make($r->all(),$r->rules()));
        $r->validated();
    }
    return $r;
};
$users=[];
foreach(['office_staff','admin','lab_officer','patient','doctor'] as $role)$users[$role]=User::whereHas('role',fn($q)=>$q->where('role_name',$role))->where('account_status','ACTIVE')->whereNull('requested_patient_code')->firstOrFail();
$staff=$users['office_staff'];$admin=$users['admin'];$labUser=$users['lab_officer'];
$newPatient=function(){
    $u=User::create(['role_id'=>Role::where('role_name','patient')->value('role_id'),'full_name'=>'Workflow Test','email'=>'check-'.bin2hex(random_bytes(6)).'@example.test','phone'=>'0771234567','password_hash'=>Hash::make('TestPass123!'),'account_status'=>'ACTIVE','email_verified_at'=>now()]);
    return app(App\Services\AccountPatientService::class)->ensure($u);
};
$schedule=function(string $date='2091-01-01',?int $doctor=null)use($staff){
    $s=DoctorSchedule::create(['doctor_id'=>$doctor ?? Doctor::where('active',1)->firstOrFail()->doctor_id,'schedule_date'=>$date,'start_time'=>'08:00:00','end_time'=>'09:00:00','slot_minutes'=>15,'capacity'=>4,'room_no'=>'OPD-07','status'=>'OPEN','created_by'=>$staff->user_id]);
    for($i=0;$i<4;$i++)AppointmentSlot::create(['schedule_id'=>$s->schedule_id,'slot_start'=>"$date 08:".str_pad($i*15,2,'0',STR_PAD_LEFT).':00','slot_end'=>Carbon\Carbon::parse("$date 08:00")->addMinutes(($i+1)*15),'slot_number'=>$i+1,'slot_status'=>'AVAILABLE']);
    return $s;
};
$invoice=function(Patient $p,?Appointment $a=null)use($staff){return Invoice::create(['invoice_no'=>'CHECK-'.bin2hex(random_bytes(8)),'patient_id'=>$p->patient_id,'appointment_id'=>$a?->appointment_id,'subtotal'=>1000,'total_amount'=>1000,'paid_amount'=>0,'balance_amount'=>1000,'status'=>'ISSUED','issued_at'=>now(),'created_by'=>$staff->user_id]);};
$cases=[];
$cases['registration, wrong OTP, verified account, welcome patient ID, login and password reset']=function()use($request,$users,$mail,$session,$check){
    $ctl=app(AuthController::class);$email='registration-'.bin2hex(random_bytes(5)).'@example.test';
    $data=['full_name'=>'Workflow Tester','email'=>$email,'phone'=>'0771234567','password'=>'TestPass123!','password_confirmation'=>'TestPass123!','terms'=>1];
    $ctl->register($request($users['patient'],$data,App\Http\Requests\RegisterPatientRequest::class));
    $check(!User::where('email',$email)->exists(),'Account created before OTP');$code=$mail->code;
    $ctl->verify($request($users['patient'],['email'=>$email,'otp'=>'000000']));
    $check(!User::where('email',$email)->exists(),'Wrong OTP accepted');
    $ctl->verify($request($users['patient'],['email'=>$email,'otp'=>$code]));
    $u=User::where('email',$email)->firstOrFail();$p=$u->patients()->firstOrFail();
    $check($u->account_status==='ACTIVE' && $mail->welcomeId===$p->patient_code,'Welcome patient ID missing');
    $ctl->logout($request($u));$r=$request($u,['email'=>$email,'password'=>'wrong']);Auth::forgetGuards();$ctl->login($r);
    $check(!Auth::check() && $u->fresh()->failed_login_count===1,'Bad password accepted');
    $ctl->login($request($u,['email'=>$email,'password'=>'TestPass123!']));
    $check(Auth::id()===$u->user_id && $u->fresh()->failed_login_count===0,'Correct login failed');
    $ctl->forgot($request($u,['email'=>$email]));$resetCode=$mail->code;
    $ctl->reset($request($u,['email'=>$email,'otp'=>$resetCode,'password'=>'Changed123!','password_confirmation'=>'Changed123!']));
    $check(Hash::check('Changed123!',$u->fresh()->password_hash),'Password reset failed');
    $ctl->reset($request($u,['email'=>$email,'otp'=>$resetCode,'password'=>'Reused123!','password_confirmation'=>'Reused123!']));
    $check(Hash::check('Changed123!',$u->fresh()->password_hash),'OTP replay accepted');
};
$cases['schedule generation and overlapping doctor rejection']=function()use($staff,$request,$check,$blocked){
    $ctl=app(StaffController::class);$data=['doctor_id'=>Doctor::where('active',1)->value('doctor_id'),'schedule_date'=>'2091-02-01','start_time'=>'08:00','end_time'=>'09:00','slot_minutes'=>15,'capacity'=>4,'room_no'=>'OPD-07'];
    $ctl->createSchedule($request($staff,$data));$s=DoctorSchedule::latest('schedule_id')->first();
    $check($s->slots()->count()===4,'Incorrect slot count');$blocked(fn()=>$ctl->createSchedule($request($staff,$data)));
    $data['schedule_date']='2091-02-02';$data['capacity']=5;$blocked(fn()=>$ctl->createSchedule($request($staff,$data)));
};
$cases['room collision across different doctors rejected']=function()use($staff,$request,$schedule,$blocked){
    $s=$schedule();$other=Doctor::where('active',1)->where('doctor_id','!=',$s->doctor_id)->value('doctor_id');
    $blocked(fn()=>app(StaffController::class)->createSchedule($request($staff,['doctor_id'=>$other,'schedule_date'=>'2091-01-01','start_time'=>'08:00','end_time'=>'09:00','slot_minutes'=>15,'capacity'=>4,'room_no'=>'07'])));
};
$cases['booking duplicate prevention, confirmation, rescheduling and cancellation']=function()use($staff,$request,$newPatient,$schedule,$check,$blocked){
    $p=$newPatient();$s=$schedule();$slots=$s->slots;$svc=app(AppointmentBookingService::class);
    $request($p->account);$a=$svc->book($p,$slots[0]->slot_id,$p->account_user_id);
    $check($a->status==='BOOKED' && $slots[0]->fresh()->slot_status==='BOOKED','Booking failed');
    $blocked(fn()=>$svc->book($p,$slots[0]->slot_id,$p->account_user_id));
    $blocked(fn()=>$svc->book($p,$slots[1]->slot_id,$p->account_user_id));
    app(StaffController::class)->confirmSession($request($staff),$s);
    $check($a->fresh()->status==='CONFIRMED','Bulk confirmation failed');
    app(PatientController::class)->reschedule($request($p->account,['slot_id'=>$slots[1]->slot_id]),$a->fresh());
    $check($a->fresh()->status==='RESCHEDULED' && $slots[0]->fresh()->slot_status==='AVAILABLE','Reschedule failed');
    app(PatientController::class)->cancel($request($p->account,['reason'=>'Workflow test cancellation']),$a->fresh());
    $check($a->fresh()->status==='CANCELLED' && $slots[1]->fresh()->slot_status==='AVAILABLE','Cancellation failed');
};
$cases['booking cutoff applies at session start']=function()use($schedule,$newPatient,$request,$blocked){
    $s=$schedule();$p=$newPatient();$request($p->account);Carbon\Carbon::setTestNow('2091-01-01 08:00:00');
    $blocked(fn()=>app(AppointmentBookingService::class)->book($p,$s->slots[1]->slot_id,$p->account_user_id));
};
$cases['disabled doctor cannot receive bookings']=function()use($schedule,$newPatient,$request,$blocked,$check){
    $s=$schedule();$s->doctor->update(['active'=>0]);$p=$newPatient();$request($p->account);
    $blocked(fn()=>app(AppointmentBookingService::class)->book($p,$s->slots[0]->slot_id,$p->account_user_id));
    $check(!app(AppointmentBookingService::class)->availableSlots($s->doctor_id,'2091-01-01')->contains('slot_id',$s->slots[0]->slot_id),'Disabled doctor still listed as bookable');
};
$cases['approved leave prevents new schedules']=function()use($staff,$request,$blocked){
    $doctor=Doctor::where('active',1)->firstOrFail();DoctorLeave::create(['doctor_id'=>$doctor->doctor_id,'requested_by'=>$doctor->user_id,'start_at'=>'2091-03-01 08:00','end_at'=>'2091-03-01 12:00','reason'=>'Workflow test','status'=>'APPROVED']);
    $blocked(fn()=>app(StaffController::class)->createSchedule($request($staff,['doctor_id'=>$doctor->doctor_id,'schedule_date'=>'2091-03-01','start_time'=>'08:00','end_time'=>'09:00','slot_minutes'=>15,'capacity'=>4,'room_no'=>'OPD-01'])));
};
$cases['patient cannot read another account appointment or edit their profile']=function()use($schedule,$newPatient,$request,$blocked){
    $p=$newPatient();$other=$newPatient();$s=$schedule();$request($p->account);$a=app(AppointmentBookingService::class)->book($p,$s->slots[0]->slot_id,$p->account_user_id);
    $blocked(fn()=>app(PatientController::class)->appointmentPayment($request($other->account),$a),403);
    $blocked(fn()=>app(PatientController::class)->editProfile($request($other->account),$p),403);
};
$cases['cancelled unpaid appointment closes its invoice']=function()use($schedule,$newPatient,$request,$invoice,$check){
    $p=$newPatient();$s=$schedule();$request($p->account);$a=app(AppointmentBookingService::class)->book($p,$s->slots[0]->slot_id,$p->account_user_id);$i=$invoice($p,$a);
    app(PatientController::class)->cancel($request($p->account,['reason'=>'Workflow cancellation']),$a);
    $check($i->fresh()->status==='VOID','Cancelled booking still has payable invoice');
    $html=app(PatientController::class)->appointmentPayment($request($p->account),$a->fresh())->render();
    $check(str_contains($html,'Invoice cancelled') && !str_contains($html,'This appointment invoice has been paid'),'Cancelled invoice incorrectly displayed as paid');
};
$cases['demo card payment validates input, settles invoice and rejects duplicates']=function()use($newPatient,$request,$invoice,$blocked,$check,$schedule){
    Mail::fake();
    $p=$newPatient();$s=$schedule();$request($p->account);
    $appointment=app(AppointmentBookingService::class)->book($p,$s->slots[0]->slot_id,$p->account_user_id);
    $i=$invoice($p,$appointment);$before=Payment::count();
    $check(Mail::sent(App\Mail\HospitalMessage::class)->count()===0,'Email sent before payment');
    $ctl=app(PatientController::class);
    $data=['cardholder_name'=>'Test User','card_number'=>'4242424242424242','expiry'=>'12/35','cvv'=>'123'];
    $blocked(fn()=>$ctl->payInvoiceByCard($request($p->account,array_replace($data,['card_number'=>'4242424242424241'])),$i));
    $check(Mail::sent(App\Mail\HospitalMessage::class)->count()===0,'Email sent before payment');
    $ctl->payInvoiceByCard($request($p->account,$data),$i);
    $check(Payment::count()===$before+1 && $i->fresh()->status==='PAID','Demo settlement failed');
    $payment=$i->payments()->firstOrFail();
    $check(Mail::sent(App\Mail\HospitalMessage::class,fn($message)=>$message->heading==='Appointment booked' && in_array('Payment: Paid by card',$message->lines) && in_array('Patient ID: '.$p->patient_code,$message->lines) && $message->hasTo($p->account->email))->count()===1,'Paid booking email missing');
    $check(Mail::sent(App\Mail\HospitalMessage::class,fn($message)=>$message->heading==='Payment recorded' && in_array('Method: CARD',$message->lines))->count()===1,'Card receipt email missing');
    $sentCount=Mail::sent(App\Mail\HospitalMessage::class)->count();
    $check(str_starts_with($payment->transaction_reference,'DEMO-CARD-') && !str_contains($payment->toJson(),$data['card_number']),'Demo reference or card privacy failed');
    $blocked(fn()=>$ctl->payInvoiceByCard($request($p->account,$data),$i->fresh()));
    $check(Mail::sent(App\Mail\HospitalMessage::class)->count()===$sentCount,'Duplicate payment resent emails');
    $check(!str_contains($ctl->appointmentPayment($request($p->account),$appointment)->render(),'Project demo'),'Project wording still displayed');
};
$cases['partial payment, overpayment rejection and final settlement']=function()use($newPatient,$invoice,$staff,$request,$check,$blocked){
    $i=$invoice($newPatient());$ctl=app(StaffController::class);$data=['payment_method'=>'CASH','amount'=>400,'confirm_payment'=>1];
    $ctl->recordPayment($request($staff,$data,App\Http\Requests\PaymentRequest::class),$i);
    $check((float)$i->fresh()->balance_amount===600.0 && $i->fresh()->status==='PARTIALLY_PAID','Partial balance wrong');
    $data['amount']=700;
    $blocked(fn()=>$ctl->recordPayment($request($staff,$data,App\Http\Requests\PaymentRequest::class),$i->fresh()));
    $data['amount']=600;$ctl->recordPayment($request($staff,$data,App\Http\Requests\PaymentRequest::class),$i->fresh());
    $check($i->fresh()->status==='PAID' && (float)$i->fresh()->balance_amount===0.0,'Final settlement failed');
};
$cases['insurance approval reconciles balance once']=function()use($newPatient,$invoice,$staff,$request,$check,$blocked){
    $i=$invoice($newPatient());$provider=App\Models\InsuranceProvider::where('active',1)->firstOrFail();$ctl=app(StaffController::class);
    $ctl->createClaim($request($staff,['invoice_id'=>$i->invoice_id,'provider_id'=>$provider->provider_id,'policy_no'=>'TEST-POLICY','requested_amount'=>600]));
    $claim=App\Models\InsuranceClaim::latest('claim_id')->first();
    $ctl->updateClaim($request($staff,['status'=>'APPROVED','approved_amount'=>600]),$claim);
    $check((float)$i->fresh()->balance_amount===400.0 && (float)$i->fresh()->insurance_covered===600.0,'Insurance balance wrong');
    $blocked(fn()=>$ctl->updateClaim($request($staff,['status'=>'APPROVED','approved_amount'=>600]),$claim->fresh()));
};
$temporaryFiles=[];$temporaryDirectories=[];
$cases['support messages, staff replies, escaping and account isolation']=function()use($newPatient,$request,$staff,$check,$blocked){
    $p=$newPatient();$other=$newPatient();$ctl=app(PatientController::class);
    $ctl->storeSupport($request($p->account,['subject'=>'Workflow support','category'=>'OTHER','message'=>'<script>bad()</script>']));
    $thread=App\Models\SupportThread::latest('support_thread_id')->first();
    $blocked(fn()=>$ctl->replySupport($request($other->account,['message'=>'Unauthorized']),$thread),403);
    app(StaffController::class)->replySupport($request($staff,['message'=>'Workflow reply','status'=>'RESOLVED']),$thread);
    $html=$ctl->support($request($p->account))->render();
    $check(str_contains($html,'Workflow reply') && !str_contains($html,'<script>bad()'),'Support escaping/reply failed');
    $check(!str_contains($ctl->support($request($other->account))->render(),'Workflow support'),'Other account sees support thread');
};
$cases['lab image upload, draft/release permissions, doctor view and patient report only']=function()use($newPatient,$request,$staff,$labUser,$users,$check,$blocked,&$temporaryFiles,&$temporaryDirectories){
    $p=$newPatient();$other=$newPatient();$test=LabTest::where('active',1)->firstOrFail();
    $ctl=app(LabBookingController::class);$billing=app(LabBillingService::class);$audit=app(AuditService::class);
    $ctl->store($request($staff,['patient_id'=>$p->patient_id,'tests'=>[$test->lab_test_id]]),$billing,$audit);
    $order=LabOrder::latest('lab_order_id')->first();
    if($order->invoice->status!=='PAID')$ctl->pay($request($staff,['method'=>'CASH','received'=>1]),$order,$billing,$audit);
    $root=sys_get_temp_dir().'/medicare-lab-check-'.bin2hex(random_bytes(6));mkdir($root);$temporaryDirectories[]=$root;
    config(['filesystems.disks.lab_private.root'=>$root]);Storage::forgetDisk('lab_private');
    $file=tempnam(sys_get_temp_dir(),'medicare-check-');$temporaryFiles[]=$file;
    file_put_contents($file,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aX1sAAAAASUVORK5CYII='));
    $upload=new Illuminate\Http\UploadedFile($file,'workflow-report.png','image/png',null,true);
    $lab=app(LabController::class);$item=$order->items()->firstOrFail();
    $lab->storeResult($request($labUser,['verify_now'=>0],App\Http\Requests\LabResultRequest::class,['report_file'=>$upload]),$item);
    $row=DB::table('clinical_attachments')->where('patient_id',$p->patient_id)->first();$temporaryFiles[]=$root.'/'.$row->stored_path;
    $check(Storage::disk('lab_private')->exists($row->stored_path),'Uploaded report missing');
    $check((int)ini_get('max_execution_time')===180,'Three-minute processing limit missing');
    $docs=app(App\Http\Controllers\SecureDocumentController::class);
    $blocked(fn()=>$docs->view($request($p->account),$row->attachment_id),403);
    $check($docs->view($request($users['doctor']),$row->attachment_id)->getStatusCode()===200,'Doctor cannot see draft report');
    $lab->storeResult($request($labUser,['verify_now'=>1,'result_text'=>'Test report values'],App\Http\Requests\LabResultRequest::class),$item->fresh());
    $check($docs->view($request($p->account),$row->attachment_id)->getStatusCode()===200,'Patient cannot see released report');
    $blocked(fn()=>$docs->view($request($other->account),$row->attachment_id),403);
    $blocked(fn()=>$docs->view($request($staff),$row->attachment_id),403);
    $html=app(PatientController::class)->labs($request($p->account))->render();
    $check(str_contains($html,'workflow-report.png') && !str_contains($html,'TEST_DOCTOR_ONLY_SUMMARY'),'Patient report display incorrect');
    $html=app(App\Http\Controllers\DoctorController::class)->history($request($users['doctor']),$p->patient_id)->getContent();
    $check(str_contains($html,'TEST_DOCTOR_ONLY_SUMMARY'),'Doctor summary missing');
    $badPath=tempnam(sys_get_temp_dir(),'medicare-check-');$temporaryFiles[]=$badPath;file_put_contents($badPath,'<?php echo "bad";');
    $bad=new Illuminate\Http\UploadedFile($badPath,'report.png','image/png',null,true);
    $blocked(fn()=>$request($labUser,[],App\Http\Requests\LabResultRequest::class,['report_file'=>$bad]));
    $blocked(fn()=>$request($labUser,[],App\Http\Requests\LabResultRequest::class));
};
$cases['leave approval blocks partially overlapping slots but allows the end boundary']=function()use($schedule,$admin,$request,$check){
    $s=$schedule();$leave=DoctorLeave::create(['doctor_id'=>$s->doctor_id,'requested_by'=>$s->doctor->user_id,'start_at'=>'2091-01-01 08:10','end_at'=>'2091-01-01 08:30','reason'=>'Workflow leave','status'=>'REQUESTED']);
    app(AdminController::class)->decideLeave($request($admin,['status'=>'APPROVED']),$leave);
    $check($s->slots[0]->fresh()->slot_status==='BLOCKED','Partially overlapping slot still available');
    $check($s->slots[2]->fresh()->slot_status==='AVAILABLE','Leave incorrectly blocks end boundary');
};
$cases['admin creates a doctor and protects own/main-admin accounts']=function()use($admin,$request,$check,$blocked){
    $ctl=app(AdminController::class);$email='doctor-'.bin2hex(random_bytes(5)).'@example.test';
    $ctl->createDoctor($request($admin,['full_name'=>'Dr. Workflow Test','email'=>$email,'password'=>'DoctorTest123!','specialty_id'=>App\Models\Specialty::where('active',1)->value('specialty_id'),'registration_no'=>'TEST-'.bin2hex(random_bytes(5)),'consultation_fee'=>1234]));
    $doctor=Doctor::whereHas('user',fn($q)=>$q->where('email',$email))->firstOrFail();
    $check(str_contains(app(StaffController::class)->schedules()->render(),'Dr. Workflow Test'),'New doctor missing from staff schedules');
    $blocked(fn()=>$ctl->toggleUser($request($admin),$admin));
    $main=User::whereHas('role',fn($q)=>$q->where('role_name','main_admin'))->firstOrFail();
    $blocked(fn()=>$ctl->toggleUser($request($admin),$main));
    $ctl->toggleUser($request($admin),$doctor->user);$check($doctor->user->fresh()->account_status==='DISABLED','Doctor disable failed');
};
$cases['cancelled appointment reminders are skipped']=function()use($schedule,$newPatient,$request,$check){
    $s=$schedule();$p=$newPatient();$request($p->account);$a=app(AppointmentBookingService::class)->book($p,$s->slots[0]->slot_id,$p->account_user_id);
    $a->update(['status'=>'CANCELLED']);
    $r=App\Models\AppointmentReminder::create(['appointment_id'=>$a->appointment_id,'reminder_type'=>'STANDARD','scheduled_for'=>now()->subMinute(),'channel'=>'EMAIL','status'=>'PENDING']);
    Illuminate\Support\Facades\Artisan::call('medicare:send-reminders');
    $check($r->fresh()->status==='SKIPPED','Cancelled appointment reminder was sent');
};
$cases['valid lab results are saved even when AI is unavailable']=function()use($newPatient,$staff,$labUser,$request,$check){
    $p=$newPatient();$test=LabTest::where('active',1)->firstOrFail();$ctl=app(LabBookingController::class);$billing=app(LabBillingService::class);$audit=app(AuditService::class);
    $ctl->store($request($staff,['patient_id'=>$p->patient_id,'tests'=>[$test->lab_test_id]]),$billing,$audit);$order=LabOrder::latest('lab_order_id')->first();
    if($order->invoice->status!=='PAID')$ctl->pay($request($staff,['method'=>'CASH','received'=>1]),$order,$billing,$audit);
    $ai=app(App\Services\AI\AiGateway::class);
    if(!$ai instanceof WorkflowAiGateway)throw new RuntimeException('Workflow AI test double is not configured.');
    $ai->failLab=true;
    try{app(LabController::class)->storeResult($request($labUser,['result_text'=>'Workflow original result','verify_now'=>0],App\Http\Requests\LabResultRequest::class),$order->items[0]);}
    finally{$ai->failLab=false;}
    $check($order->items[0]->fresh()->result->result_text==='Workflow original result','Original result lost on AI failure');
};
$cases['patient family profile accepts omitted optional fields and can be updated']=function()use($newPatient,$request,$check){
    $p=$newPatient();$data=['relationship_to_account'=>'Child','first_name'=>'Family','last_name'=>'Tester','sex'=>'UNKNOWN'];
    $ctl=app(PatientController::class);$ctl->storeProfile($request($p->account,$data,App\Http\Requests\PatientProfileRequest::class));
    $family=$p->account->patients()->where('relationship_to_account','Child')->firstOrFail();
    $data['first_name']='Updated';$ctl->updateProfile($request($p->account,$data,App\Http\Requests\PatientProfileRequest::class),$family);
    $check($family->fresh()->first_name==='Updated' && $family->patient_id!==$p->patient_id,'Family profile update failed');
};
$cases['admin catalog creation with optional fields omitted']=function()use($admin,$request){
    foreach(['specialty','lab_test','condition'] as $type)app(AdminController::class)->storeCatalog($request($admin,['catalog'=>$type,'name'=>'Workflow '.bin2hex(random_bytes(4))]));
};
$cases['disabled accounts cannot reactivate through email verification']=function()use($newPatient,$request,$blocked){
    $p=$newPatient();$u=$p->account;$u->update(['account_status'=>'DISABLED']);
    $blocked(fn()=>app(AuthController::class)->resend($request($u,['email'=>$u->email])));
};
$cases['AI routing uses local fallback without external requests']=function()use($check){
    $keys=['services.gemini.key'=>config('services.gemini.key'),'services.anthropic.key'=>config('services.anthropic.key'),'ai.local_fallback'=>config('ai.local_fallback')];
    config(['services.gemini.key'=>'','services.anthropic.key'=>'','ai.local_fallback'=>true]);
    try{
        $ai=new App\Services\AI\AiGateway(new App\Services\AI\GeminiProvider,new App\Services\AI\ClaudeProvider,new App\Services\AI\LocalClinicalRoutingProvider);
        $result=$ai->recommendSpecialty('A mild headache since yesterday',['General Medicine','Cardiology']);
        $check(in_array($result['specialty'],['General Medicine','Cardiology']) && $result['confidence']>=0 && $result['confidence']<=1,'AI routing contract failed');
        $check(!empty($result['disclaimer']),'Routing disclaimer missing');
    }finally{config($keys);}
};
$failed=[];$passed=0;
$cases['weekly trends exclude consultations at the next week boundary']=function()use($schedule,$newPatient,$request,$check){
    $s=$schedule('2091-06-04');$start=Carbon\Carbon::parse('2091-06-04')->startOfWeek();$condition=App\Models\DiagnosisCondition::where('active',1)->firstOrFail();
    foreach([$start->copy()->addDay(),$start->copy()->addWeek()] as $index=>$completed){
        $p=$newPatient();$request($p->account);$a=app(AppointmentBookingService::class)->book($p,$s->slots[$index]->slot_id,$p->account_user_id);$a->update(['status'=>'COMPLETED']);
        $c=App\Models\Consultation::create(['appointment_id'=>$a->appointment_id,'patient_id'=>$p->patient_id,'doctor_id'=>$s->doctor_id,'started_at'=>$completed,'completed_at'=>$completed,'clinical_notes'=>'Workflow test','diagnosis_text'=>'Workflow test']);
        DB::table('consultation_conditions')->insert(['consultation_id'=>$c->consultation_id,'condition_id'=>$condition->condition_id,'is_primary'=>1,'finalized'=>1,'created_at'=>now()]);
    }
    app(App\Services\TrendService::class)->generate($start);
    $row=App\Models\WeeklyDiseaseTrend::whereDate('week_start',$start)->where('condition_id',$condition->condition_id)->firstOrFail();
    $check((int)$row->case_count===1,'Next week boundary double counted');
};
$filter=$argv[1] ?? '';
$cases['OTP password reset enforces five attempts and hour lock']=function()use($newPatient,$request,$mail,$check,$blocked){
    Carbon\Carbon::setTestNow(now()->startOfSecond());$p=$newPatient();$u=$p->account;$ctl=app(AuthController::class);
    $ctl->forgot($request($u,['email'=>$u->email]));
    $record=App\Models\EmailOtp::where('user_id',$u->user_id)->latest('otp_id')->first();
    $check($record->expires_at->timestamp-now()->timestamp===180,'Reset OTP duration incorrect');
    $data=['email'=>$u->email,'otp'=>'000000','password'=>'Changed123!','password_confirmation'=>'Changed123!'];
    for($n=0;$n<5;$n++)$ctl->reset($request($u,$data));
    $blocked(fn()=>$ctl->forgot($request($u,['email'=>$u->email])));
    $blocked(fn()=>$ctl->reset($request($u,array_replace($data,['otp'=>$mail->code]))));
    Carbon\Carbon::setTestNow(now()->addHour());$ctl->forgot($request($u,['email'=>$u->email]));
    $ctl->reset($request($u,array_replace($data,['otp'=>$mail->code])));
    $check(Hash::check('Changed123!',$u->fresh()->password_hash),'Reset after lock failed');
};
$cases['OTP three minutes five attempts and one hour lock']=function()use($request,$users,$mail,$session,$check,$blocked){
    Carbon\Carbon::setTestNow(now()->startOfSecond());$ctl=app(AuthController::class);
    $email='otp-'.bin2hex(random_bytes(5)).'@example.test';
    $data=['full_name'=>'OTP Tester','email'=>$email,'phone'=>'0771234567','password'=>'TestPass123!','password_confirmation'=>'TestPass123!','terms'=>1];
    $ctl->register($request($users['patient'],$data,App\Http\Requests\RegisterPatientRequest::class));
    $check($session->get('pending_registration')['expires_at']-now()->timestamp===180,'OTP duration is not three minutes');
    $code=$mail->code;
    for($n=1;$n<=5;$n++)$ctl->verify($request($users['patient'],['email'=>$email,'otp'=>'000000']));
    $blocked(fn()=>$ctl->verify($request($users['patient'],['email'=>$email,'otp'=>$code])));
    $blocked(fn()=>$ctl->resend($request($users['patient'],['email'=>$email])));
    $blocked(fn()=>$ctl->register($request($users['patient'],$data,App\Http\Requests\RegisterPatientRequest::class)));
    $view=$ctl->showVerify($request($users['patient']));
    $check($view->getData()['lockedUntil']-now()->timestamp===3600 && !$view->getData()['canResend'],'Lock display incorrect');
    Carbon\Carbon::setTestNow(now()->addHour());
    $ctl->resend($request($users['patient'],['email'=>$email]));
    $check($session->get('pending_registration')['attempts']===0,'Resend did not reset attempts');
    $ctl->verify($request($users['patient'],['email'=>$email,'otp'=>$mail->code]));
    $check(User::where('email',$email)->exists(),'Verification after lock failed');
};
$cases['OTP send failure and unknown reset email show errors']=function()use($request,$users,$mail,$session,$check){
    $ctl=app(AuthController::class);$mail->failOtp=true;
    try{
        $ctl->register($request($users['patient'],['full_name'=>'Mail Tester','email'=>'missing-'.bin2hex(random_bytes(5)).'@example.test','phone'=>'0771234567','password'=>'TestPass123!','password_confirmation'=>'TestPass123!','terms'=>1],App\Http\Requests\RegisterPatientRequest::class));
        $check($session->get('errors')->has('email'),'SMTP failure not shown');
        $check($session->get('pending_registration')['expires_at']<=now()->timestamp,'Failed send cannot be retried');
        $ctl->forgot($request($users['patient'],['email'=>'absent-'.bin2hex(random_bytes(5)).'@example.test']));
        $check(str_contains($session->get('errors')->first('email'),'No registered account'),'Unknown email message missing');
    }finally{$mail->failOtp=false;}
};
$cases['hospital cash and terminal payments require refund collection']=function()use($newPatient,$schedule,$invoice,$request,$staff,$check){
    foreach(['CASH','CARD'] as $method){
        Mail::fake();$p=$newPatient();$s=$schedule($method==='CASH'?'2092-01-01':'2092-01-02');$request($p->account);
        $a=app(AppointmentBookingService::class)->book($p,$s->slots[0]->slot_id,$p->account_user_id);$i=$invoice($p,$a);
        app(StaffController::class)->recordPayment($request($staff,['payment_method'=>$method,'amount'=>1000,'confirm_payment'=>1,'transaction_reference'=>'TERMINAL-TEST'],App\Http\Requests\PaymentRequest::class),$i);
        app(PatientController::class)->cancel($request($p->account,['reason'=>'Refund collection test']),$a);
        $payment=$i->payments()->firstOrFail();
        $check($payment->status==='COMPLETED' && (float)$i->fresh()->paid_amount===1000.0,'Hospital money incorrectly automatically refunded');
        $check(str_contains($payment->notes,'Hospital refund awaiting collection'),'Collection marker missing');
        $check(Mail::sent(App\Mail\HospitalMessage::class,fn($m)=>$m->heading==='Appointment refund' && str_contains(implode(' ',$m->lines),'collect your hospital payment refund of LKR 1,000.00') && !str_contains(implode(' ',$m->lines),'marked as refunded'))->count()===1,'Hospital collection email incorrect');
    }
};
$cases['seven day cancellation refund and reschedule deadline']=function()use($newPatient,$schedule,$invoice,$request,$check,$blocked){
    Mail::fake();$p=$newPatient();$s=$schedule();$request($p->account);$svc=app(AppointmentBookingService::class);$ctl=app(PatientController::class);
    $a=$svc->book($p,$s->slots[0]->slot_id,$p->account_user_id);$a->update(['created_at'=>now()->subDays(6)]);$original=$a->created_at->toDateTimeString();
    $i=$invoice($p,$a);
    $ctl->payInvoiceByCard($request($p->account,['cardholder_name'=>'Test User','card_number'=>'4242424242424242','expiry'=>'12/35','cvv'=>'123']),$i);
    $ctl->reschedule($request($p->account,['slot_id'=>$s->slots[1]->slot_id]),$a->fresh());
    $check($a->fresh()->created_at->toDateTimeString()===$original,'Rescheduling resets seven day clock');
    $ctl->cancel($request($p->account,['reason'=>'Within one week']),$a->fresh());
    $check($i->fresh()->status==='VOID' && $i->payments()->first()->status==='REFUNDED','Paid cancellation not refunded');
    $check(Mail::sent(App\Mail\HospitalMessage::class,fn($m)=>$m->heading==='Appointment refund' && in_array('Amount: LKR 1,000.00',$m->lines))->count()===1,'Refund amount email missing');
    $blocked(fn()=>$ctl->cancel($request($p->account,['reason'=>'Repeat']),$a->fresh()));
    $a=$svc->book($p,$s->slots[0]->slot_id,$p->account_user_id);
    $a->update(['created_at'=>now()->subDays(7)]);
    $blocked(fn()=>$ctl->cancel($request($p->account,['reason'=>'Too late']),$a->fresh()));
    $blocked(fn()=>$ctl->rescheduleForm($request($p->account),$a->fresh()));
    $blocked(fn()=>$ctl->reschedule($request($p->account,['slot_id'=>$s->slots[2]->slot_id]),$a->fresh()));
    $check(!$a->fresh()->allowsPatientChanges(),'Expired actions still enabled');
    $a->update(['created_at'=>now(),'status'=>'COMPLETED']);
    $blocked(fn()=>$ctl->cancel($request($p->account,['reason'=>'Already consulted']),$a->fresh()));
};
foreach($cases as $name=>$case){if($filter!=='' && !str_contains($name,$filter))continue;DB::beginTransaction();$session->flush();try{$case();$passed++;echo "PASS $name\n";}catch(Throwable $e){$failed[]=$name;echo "FAIL $name: ".$e->getMessage()."\n";}finally{DB::rollBack();Carbon\Carbon::setTestNow();}}
echo "$passed workflow groups passed; ".count($failed)." failed. All database fixtures rolled back; no external requests sent.\n";
foreach($temporaryFiles as $file)if(is_file($file))unlink($file);
foreach($temporaryDirectories as $directory)if(is_dir($directory))rmdir($directory);
exit($failed?1:0);
