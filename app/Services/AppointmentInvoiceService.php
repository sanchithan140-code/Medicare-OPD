<?php
namespace App\Services;
use App\Models\{Appointment,Invoice};
class AppointmentInvoiceService
{
    public function refundCancellation(Appointment $appointment): array
    {
        $refunded=0;$hospitalDue=0;
        foreach(Invoice::where('appointment_id',$appointment->appointment_id)->lockForUpdate()->get() as $invoice){
            $invoiceRefund=0;
            foreach($invoice->payments()->where('status','COMPLETED')->lockForUpdate()->get() as $payment){
                if($payment->payment_method==='INSURANCE')continue;
                $online=$payment->payment_method==='CARD'
                    && \App\Models\User::whereKey($payment->received_by)->whereHas('role',fn($q)=>$q->where('role_name','patient'))->exists()
                    && (str_starts_with((string)$payment->transaction_reference,'DEMO-CARD-') || str_starts_with((string)$payment->transaction_reference,'CARD-'));
                if(!$online){
                    $hospitalDue+=(float)$payment->amount;
                    $payment->update(['notes'=>mb_substr('Hospital refund awaiting collection; '.($payment->notes ?? ''),0,500)]);
                    app(AuditService::class)->record('payment.hospital_refund_pending',$payment,[],['appointment_id'=>$appointment->appointment_id,'amount'=>$payment->amount]);
                    continue;
                }
                $refunded+=(float)$payment->amount;$invoiceRefund+=(float)$payment->amount;
                $payment->update(['status'=>'REFUNDED','notes'=>mb_substr(($payment->notes ? $payment->notes.'; ' : '').'Refund recorded on cancellation '.now()->toDateTimeString(),0,500)]);
                app(AuditService::class)->record('payment.refunded',$payment,[],['appointment_id'=>$appointment->appointment_id,'amount'=>$payment->amount]);
            }
            $invoice->update(['status'=>'VOID','paid_amount'=>max(0,(float)$invoice->paid_amount-$invoiceRefund),'balance_amount'=>0]);
        }
        return ['online'=>$refunded,'hospital'=>$hospitalDue];
    }
    // Called inside the cancellation transaction; paid records remain for refund review.
    public function cancelUnpaid(Appointment $appointment):void
    {
        foreach(Invoice::where('appointment_id',$appointment->appointment_id)->lockForUpdate()->get() as $invoice){
            if((float)$invoice->paid_amount===0.0 && (float)$invoice->insurance_covered===0.0 && !$invoice->payments()->where('status','COMPLETED')->exists()){
                $invoice->update(['status'=>'VOID','balance_amount'=>0]);
            }
        }
    }
}
