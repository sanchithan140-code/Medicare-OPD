<?php
// Integration checks: all fixtures are rolled back; no messages are sent.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use App\Models\{User,Patient,LabTest,LabOrder,Payment};
Illuminate\Support\Facades\Mail::fake();
view()->share('errors',new Illuminate\Support\ViewErrorBag());
DB::beginTransaction();
try {
    $staff=User::whereHas('role',fn($q)=>$q->where('role_name','office_staff'))->firstOrFail();
    \Illuminate\Support\Facades\Auth::guard()->setUser($staff);
    $request=function($data)use($staff,$app){
        $r=Illuminate\Http\Request::create('/staff/lab-bookings','POST',$data);
        $r->setUserResolver(fn()=>$staff);$app->instance('request',$r);return $r;
    };
    $check=function($yes,$label){if(!$yes)throw new Exception($label);echo "PASS $label\n";};
    $blocked=function($fn)use($check){
        try{$fn();throw new Exception('Expected rejection');}
        catch(Symfony\Component\HttpKernel\Exception\HttpException $e){$check($e->getStatusCode()===422,'unsafe action rejected');}
    };
    $ctl=app(App\Http\Controllers\LabBookingController::class);
    $billing=app(App\Services\LabBillingService::class);$audit=app(App\Services\AuditService::class);
    $tests=LabTest::where('active',1)->take(2)->get();
    $tests[0]->update(['standard_fee'=>100]);$tests[1]->update(['standard_fee'=>200]);
    $patient=Patient::where('active',1)->firstOrFail();
    $before=Payment::where('status','COMPLETED')->sum('amount');
    $create=function($ids)use($ctl,$request,$patient,$billing,$audit){
        $ctl->store($request(['patient_id'=>$patient->patient_id,'tests'=>$ids]),$billing,$audit);
        return LabOrder::latest('lab_order_id')->first();
    };
    $order=$create([$tests[0]->lab_test_id]);
    $check((float)$order->invoice->total_amount===100.0,'staff order creates quoted invoice');
    $blocked(fn()=>$billing->requirePaid($order));
    $ctl->price($request(['standard_fee'=>350]),$tests[0],$audit);
    $ctl->edit($request(['tests'=>$tests->pluck('lab_test_id')->all()]),$order,$billing,$audit);
    $order->refresh();
    $check((float)$order->invoice->total_amount===300.0,'editing preserves existing quote');
    $check(str_contains($ctl->payment($order)->getContent(),'Confirm payment'),'payment page renders');
    $ctl->pay($request(['method'=>'CARD','reference'=>'TEST-TERMINAL','received'=>1]),$order,$billing,$audit);
    $order->refresh();$billing->requirePaid($order);
    $check($order->invoice->status==='PAID' && (float)$order->invoice->balance_amount===0.0,'card payment settles invoice');
    $blocked(fn()=>$ctl->pay($request(['method'=>'CARD','received'=>1]),$order,$billing,$audit));
    $blocked(fn()=>$ctl->edit($request(['tests'=>[$tests[0]->lab_test_id]]),$order,$billing,$audit));
    $check((float)Payment::where('status','COMPLETED')->sum('amount')-(float)$before===300.0,'admin revenue counts payment exactly once');
    $html=app(App\Http\Controllers\WalkInController::class)->receipt($order->invoice)->getContent();
    $check(str_contains($html,$order->lab_order_no) && str_contains($html,'TEST-TERMINAL'),'receipt includes lab reference and payment');
    $lab=app(App\Http\Controllers\LabController::class);
    $lab->updateStatus($request(['status'=>'IN_PROGRESS']),$order);
    $check($order->fresh()->status==='IN_PROGRESS','paid order can begin processing');
    $same=$lab->updateStatus($request(['status'=>'IN_PROGRESS']),$order->fresh());
    $check(str_contains($same->getSession()->get('success'),'Already updated'),'same status returns friendly confirmation');
    $order->update(['status'=>'COMPLETED']);
    $same=$lab->updateStatus($request(['status'=>'COMPLETED']),$order->fresh());
    $check(str_contains($same->getSession()->get('success'),'Already updated'),'completed status resubmission is accepted without changes');
    $order->update(['status'=>'IN_PROGRESS']);
    $blocked(fn()=>$lab->updateStatus($request(['status'=>'CANCELLED']),$order->fresh()));
    $cancel=$create([$tests[1]->lab_test_id]);
    $lab->updateStatus($request(['status'=>'CANCELLED']),$cancel);
    $check($cancel->fresh()->invoice->status==='VOID','unpaid cancellation voids invoice');
    $blocked(fn()=>$ctl->pay($request(['method'=>'CASH','received'=>1]),$cancel,$billing,$audit));
    $cash=$create([$tests[1]->lab_test_id]);
    $ctl->pay($request(['method'=>'CASH','received'=>1]),$cash,$billing,$audit);
    $check($cash->fresh()->invoice->payments->first()->payment_method==='CASH','cash payment recorded');
    $check(strlen($ctl->index($request(['patient_code'=>$patient->patient_code]))->render())>100,'booking page renders');
    $check(strlen($lab->orders($request([]))->render())>100,'lab queue renders reception orders');
    $check(strlen(app(App\Http\Controllers\StaffController::class)->billing($request([]))->render())>100,'billing page renders laboratory invoices');
    $appointment=App\Models\Appointment::whereIn('status',['CONFIRMED','CHECKED_IN'])->whereDoesntHave('consultation')->firstOrFail();
    $doctorUser=$appointment->doctor->user;\Illuminate\Support\Facades\Auth::guard()->setUser($doctorUser);
    $data=['chief_complaint'=>'Integration check','clinical_notes'=>'Integration check only','diagnosis_text'=>'Integration check','treatment_plan'=>'Integration check','patient_summary'=>null,'follow_up_date'=>null,'lab_test_ids'=>[$tests[0]->lab_test_id]];
    $consultationRequest=App\Http\Requests\ConsultationRequest::create('/doctor/consultation','POST',$data);
    $consultationRequest->setUserResolver(fn()=>$doctorUser);
    $consultationRequest->setValidator(Illuminate\Support\Facades\Validator::make($data,$consultationRequest->rules()));
    $app->instance('request',$consultationRequest);
    app(App\Http\Controllers\DoctorController::class)->storeConsultation($consultationRequest,$appointment);
    $doctorOrder=LabOrder::latest('lab_order_id')->first();
    $check($doctorOrder->consultation_id && $doctorOrder->invoice->status==='ISSUED' && (float)$doctorOrder->invoice->total_amount===350.0,'doctor referral automatically invoices current test price');
    $blocked(fn()=>$billing->requirePaid($doctorOrder));
    echo "All checks passed. Fixtures rolled back.\n";
} finally {
    DB::rollBack();
}
