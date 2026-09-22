<?php
namespace App\Services;
use App\Models\{LabOrder,Invoice,InvoiceItem};
use Illuminate\Support\Facades\DB;
class LabBillingService
{
    public function invoice(LabOrder $order, int $actor): Invoice
    {
        return DB::transaction(function()use($order,$actor){
            $order=LabOrder::whereKey($order->lab_order_id)->lockForUpdate()->firstOrFail();
            $existing=Invoice::where('lab_order_id',$order->lab_order_id)->first();
            if($existing)return $existing;
            $items=$order->items()->with('test')->where('status','!=','CANCELLED')->get();
            $total=$items->sum('fee');
            $invoice=Invoice::create(['invoice_no'=>'INV-'.strtoupper(bin2hex(random_bytes(8))),
                'patient_id'=>$order->patient_id,'lab_order_id'=>$order->lab_order_id,
                'subtotal'=>$total,'total_amount'=>$total,'balance_amount'=>$total,'paid_amount'=>0,
                'status'=>$total>0?'ISSUED':'PAID','issued_at'=>now(),'created_by'=>$actor]);
            foreach($items as $item)InvoiceItem::create(['invoice_id'=>$invoice->invoice_id,'item_type'=>'LABORATORY',
                'description'=>$item->test->test_name,'quantity'=>1,'unit_price'=>$item->fee,'line_total'=>$item->fee]);
            return $invoice;
        });
    }
    public function requirePaid(LabOrder $order): void
    {
        abort_unless($order->status!=='CANCELLED' && Invoice::where('lab_order_id',$order->lab_order_id)
            ->where('status','PAID')->where('balance_amount',0)->exists(),422,'Confirm laboratory payment before collecting samples or processing results.');
    }
}
