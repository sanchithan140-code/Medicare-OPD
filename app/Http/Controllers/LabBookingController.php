<?php
namespace App\Http\Controllers;
use App\Models\{LabOrder,LabTest,LabOrderItem,Patient,Payment,Invoice};
use App\Services\{LabBillingService,AuditService};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
class LabBookingController extends Controller
{
    public function index(Request $request)
    {
        $data=$request->validate(['patient_code'=>['nullable','string','max:50']]);
        return view('staff.lab-booking',[
            'tests'=>LabTest::orderBy('test_name')->get(),
            'patient'=>empty($data['patient_code'])?null:Patient::where('patient_code',$data['patient_code'])->where('active',1)->first(),
            'orders'=>LabOrder::with('patient','items.test','invoice')->when($data['patient_code'] ?? null,fn($q,$code)=>$q->whereHas('patient',fn($p)=>$p->where('patient_code',$code)))->latest('lab_order_id')->paginate(15)->withQueryString(),
        ]);
    }
    public function store(Request $request,LabBillingService $billing,AuditService $audit)
    {
        $data=$request->validate(['patient_id'=>['required','exists:patients,patient_id'],'tests'=>['required','array','min:1'],'tests.*'=>['integer','distinct','exists:lab_tests,lab_test_id']]);
        $order=DB::transaction(function()use($data,$request,$billing){
            Patient::whereKey($data['patient_id'])->where('active',1)->lockForUpdate()->firstOrFail();
            $order=LabOrder::create(['lab_order_no'=>'LAB-'.strtoupper(bin2hex(random_bytes(8))),'patient_id'=>$data['patient_id'],'status'=>'PENDING','priority'=>'ROUTINE','ordered_at'=>now()]);
            $tests=LabTest::whereIn('lab_test_id',$data['tests'])->where('active',1)->lockForUpdate()->get();
            abort_unless($tests->count()===count($data['tests']),422,'A selected test is inactive.');
            foreach($tests as $test)LabOrderItem::create(['lab_order_id'=>$order->lab_order_id,'lab_test_id'=>$test->lab_test_id,'status'=>'PENDING','fee'=>$test->standard_fee]);
            $billing->invoice($order,$request->user()->user_id);
            return $order;
        });
        $audit->record('lab.staff_booked',$order);
        return redirect()->route('lab-payment.show',$order);
    }
    public function price(Request $request,LabTest $test,AuditService $audit)
    {
        $data=$request->validate(['standard_fee'=>['required','numeric','min:0','max:999999.99']]);
        $old=$test->standard_fee;$test->update($data);$audit->record('lab.price_updated',$test,['standard_fee'=>$old],$data);
        return back()->with('success','Test price updated for new orders. Existing quotes are unchanged.');
    }
    public function edit(Request $request,LabOrder $order,LabBillingService $billing,AuditService $audit)
    {
        $data=$request->validate(['tests'=>['required','array','min:1'],'tests.*'=>['integer','distinct','exists:lab_tests,lab_test_id']]);
        DB::transaction(function()use($data,$order,$billing,$request){
            $order=LabOrder::whereKey($order->lab_order_id)->lockForUpdate()->firstOrFail();
            abort_unless($order->status==='PENDING' && !$order->consultation_id,422,'Only unprocessed staff orders can be edited.');
            $invoice=$billing->invoice($order,$request->user()->user_id);
            $invoice=Invoice::whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            abort_if($invoice->payments()->exists() || $invoice->status==='PAID' || $order->items()->where('status','!=','PENDING')->exists() || $order->items()->whereHas('result')->exists(),422,'This order has payments or processing records and cannot be edited.');
            $tests=LabTest::whereIn('lab_test_id',$data['tests'])->where('active',1)->get();
            abort_unless($tests->count()===count($data['tests']),422,'A selected test is inactive.');
            $existing=$order->items()->get()->keyBy('lab_test_id');
            $order->items()->delete();$invoice->items()->delete();
            $total=0;
            foreach($tests as $test){$fee=$existing->get($test->lab_test_id)?->fee ?? $test->standard_fee;$total+=$fee;
                LabOrderItem::create(['lab_order_id'=>$order->lab_order_id,'lab_test_id'=>$test->lab_test_id,'status'=>'PENDING','fee'=>$fee]);
                \App\Models\InvoiceItem::create(['invoice_id'=>$invoice->invoice_id,'item_type'=>'LABORATORY','description'=>$test->test_name,'quantity'=>1,'unit_price'=>$fee,'line_total'=>$fee]);}
            $invoice->update(['subtotal'=>$total,'total_amount'=>$total,'balance_amount'=>$total,'status'=>$total>0?'ISSUED':'PAID']);
        });
        $audit->record('lab.staff_order_edited',$order);return back()->with('success','Tests and invoice updated.');
    }
    public function payment(LabOrder $order)
    {
        return response()->view('lab.payment',['order'=>$order->load('patient','items.test','invoice.payments')])->header('Cache-Control','private, no-store');
    }
    public function pay(Request $request,LabOrder $order,LabBillingService $billing,AuditService $audit)
    {
        $data=$request->validate(['method'=>['required','in:CASH,CARD'],'reference'=>['nullable','string','max:100'],'received'=>['accepted']]);
        $invoice=DB::transaction(function()use($order,$request,$data,$billing){
            $order=LabOrder::whereKey($order->lab_order_id)->lockForUpdate()->firstOrFail();
            abort_if($order->status==='CANCELLED',422,'Cancelled orders cannot receive payment.');
            $invoice=$billing->invoice($order,$request->user()->user_id);
            $invoice=Invoice::whereKey($invoice->invoice_id)->lockForUpdate()->firstOrFail();
            abort_unless(in_array($invoice->status,['ISSUED','PARTIALLY_PAID']) && $invoice->balance_amount>0,422,'This invoice is already paid or closed.');
            Payment::create(['payment_no'=>'PAY-'.strtoupper(bin2hex(random_bytes(8))),'invoice_id'=>$invoice->invoice_id,'payment_method'=>$data['method'],'amount'=>$invoice->balance_amount,'transaction_reference'=>$data['reference'] ?? null,'status'=>'COMPLETED','received_by'=>$request->user()->user_id,'paid_at'=>now(),'notes'=>$data['method']==='CARD'?'Paid at hospital card terminal':'Cash received for laboratory tests']);
            $invoice->update(['paid_amount'=>$invoice->paid_amount+$invoice->balance_amount,'balance_amount'=>0,'status'=>'PAID']);return $invoice;
        });
        $audit->record('lab.payment_received',$invoice);return back()->with('success','Payment confirmed. Laboratory processing can begin.');
    }
}
