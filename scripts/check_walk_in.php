<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\{DB,Mail};
use App\Models\{User,Patient,DoctorSchedule,AppointmentSlot,Invoice,Role};
Mail::fake();
$app->instance(App\Services\AI\AiGateway::class,new class extends App\Services\AI\AiGateway {
    public function __construct() {}
    public function explainNoShowRisk(array $features):array{return [];}
});
view()->share('errors',new Illuminate\Support\ViewErrorBag());
DB::beginTransaction();
try {
$staff=User::whereHas('role',fn($q)=>$q->where('role_name','office_staff'))->firstOrFail();\Illuminate\Support\Facades\Auth::guard()->setUser($staff);
$s=DoctorSchedule::firstOrFail();
$testSchedule=DoctorSchedule::create(['doctor_id'=>$s->doctor_id,'schedule_date'=>'2090-01-01','start_time'=>'08:00:00','end_time'=>'09:00:00','slot_minutes'=>15,'capacity'=>4,'room_no'=>'TEST','status'=>'OPEN','created_by'=>$staff->user_id]);
$slot=AppointmentSlot::create(['schedule_id'=>$testSchedule->schedule_id,'slot_start'=>'2090-01-01 08:00:00','slot_end'=>'2090-01-01 08:15:00','slot_number'=>1,'slot_status'=>'AVAILABLE']);
$c=$app->make(App\Http\Controllers\WalkInController::class);
$r=Illuminate\Http\Request::create('/staff/walk-in','POST',['first_name'=>'Walkin','last_name'=>'Test','sex'=>'UNKNOWN','slot_id'=>$slot->slot_id,'payment_method'=>'CASH','payment_received'=>'1']);$r->setUserResolver(fn()=>$staff);$app->instance('request',$r);
$c->store($r,$app->make(App\Services\AppointmentBookingService::class),$app->make(App\Services\PatientCodeService::class),$app->make(App\Services\AuditService::class));
$i=Invoice::whereHas('appointment',fn($q)=>$q->where('slot_id',$slot->slot_id))->firstOrFail();$p=$i->patient;
if($i->status!=='PAID'||$i->appointment->status!=='CONFIRMED'||!$p->account->is_walk_in)throw new Exception('Booking/payment failed');
if(!str_contains($c->receipt($i)->getContent(),$p->patient_code))throw new Exception('Receipt failed');
$cardSchedule=DoctorSchedule::create(['doctor_id'=>$s->doctor_id,'schedule_date'=>'2090-01-02','start_time'=>'08:00:00','end_time'=>'09:00:00','slot_minutes'=>15,'capacity'=>4,'room_no'=>'TEST','status'=>'OPEN','created_by'=>$staff->user_id]);
$cardSlot=AppointmentSlot::create(['schedule_id'=>$cardSchedule->schedule_id,'slot_start'=>'2090-01-02 08:00:00','slot_end'=>'2090-01-02 08:15:00','slot_number'=>1,'slot_status'=>'AVAILABLE']);
$cardRequest=Illuminate\Http\Request::create('/staff/walk-in','POST',['patient_id'=>$p->patient_id,'slot_id'=>$cardSlot->slot_id,'payment_method'=>'CARD','transaction_reference'=>'TERMINAL-TEST','payment_received'=>'1']);$cardRequest->setUserResolver(fn()=>$staff);
$c->store($cardRequest,$app->make(App\Services\AppointmentBookingService::class),$app->make(App\Services\PatientCodeService::class),$app->make(App\Services\AuditService::class));
$cardInvoice=Invoice::whereHas('appointment',fn($q)=>$q->where('slot_id',$cardSlot->slot_id))->firstOrFail();
if($cardInvoice->payments->first()->payment_method!=='CARD'||$cardInvoice->patient_id!==$p->patient_id)throw new Exception('Terminal payment failed');
if(!str_contains($c->receipt($cardInvoice)->getContent(),'TERMINAL-TEST'))throw new Exception('Terminal receipt failed');
$count=Patient::count();
try{$c->store($r,$app->make(App\Services\AppointmentBookingService::class),$app->make(App\Services\PatientCodeService::class),$app->make(App\Services\AuditService::class));throw new Exception('Duplicate accepted');}catch(Illuminate\Validation\ValidationException $e){}
if(Patient::count()!==$count)throw new Exception('Duplicate patient left behind');
$online=User::create(['role_id'=>Role::where('role_name','patient')->value('role_id'),'full_name'=>'Walkin Test','email'=>'link-test@example.com','password_hash'=>'unused-test','account_status'=>'ACTIVE','email_verified_at'=>now(),'requested_patient_code'=>$p->patient_code]);
$before=$p->patient_id;
try{$app->make(App\Services\AccountPatientService::class)->ensure($online);throw new Exception('Pending account got duplicate profile');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){if($e->getStatusCode()!==403)throw $e;}
$r=Illuminate\Http\Request::create('/staff/patient-links','POST',['patient_code'=>$p->patient_code,'identity_verified'=>'1','verification_note'=>'Identity checked against test hospital record']);$r->setUserResolver(fn()=>$staff);
$c->link($r,$online,$app->make(App\Services\AuditService::class));$p->refresh();
if($p->patient_id!==$before||$p->account_user_id!==$online->user_id||$online->fresh()->requested_patient_code!==null)throw new Exception('Link failed');
if($i->fresh()->patient_id!==$before)throw new Exception('History changed');
$c->links()->render();
echo "PASS: walk-in creation, cash payment, confirmed booking, receipt, duplicate-submit rollback, pending-account protection, verified linking and preserved history.\n";
} finally {DB::rollBack();}