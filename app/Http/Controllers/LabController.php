<?php

namespace App\Http\Controllers;

use App\Http\Requests\LabResultRequest;
use App\Models\LabOrder;
use App\Models\LabOrderItem;
use App\Models\LabResult;
use App\Services\AI\AiGateway;
use App\Services\AuditService;
use App\Services\HospitalMailService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LabController extends Controller
{
    public function __construct(
        private readonly AiGateway $ai,
        private readonly AuditService $audit,
        private readonly NotificationService $notifications,
        private readonly HospitalMailService $mail,
    ) {}

    public function dashboard()
    {
        return view('lab.dashboard',[
            'pending' => LabOrder::whereIn('status',['PENDING','SAMPLE_COLLECTED','IN_PROGRESS'])->count(),
            'completedToday' => LabOrder::where('status','COMPLETED')->whereDate('completed_at',today())->count(),
            'orders' => LabOrder::with('patient','doctor.user','items.test')->latest('lab_order_id')->limit(8)->get(),
        ]);
    }

    public function orders(Request $request)
    {
        $validated = $request->validate(['status' => ['nullable','in:PENDING,SAMPLE_COLLECTED,IN_PROGRESS,COMPLETED,CANCELLED']]);
        $orders = LabOrder::with('patient','doctor.user','items.test','items.result','invoice')
            ->when($validated['status'] ?? null, fn ($query,$status) => $query->where('status',$status))
            ->latest('lab_order_id')->paginate(20)->withQueryString();

        return view('lab.orders',compact('orders'));
    }

    public function updateStatus(Request $request, LabOrder $order)
    {
        $data = $request->validate(['status'=>['required','in:PENDING,SAMPLE_COLLECTED,IN_PROGRESS,COMPLETED,CANCELLED']]);
        $changed = DB::transaction(function () use ($order,$data) {
        $order=LabOrder::whereKey($order->lab_order_id)->lockForUpdate()->firstOrFail();
        if ($order->status === $data['status']) { return false; }
        if ($data['status'] === 'COMPLETED') {
            throw \Illuminate\Validation\ValidationException::withMessages(['status'=>'Complete the test results first. The order status updates automatically.']);
        }
        $invoice=$order->invoice()->lockForUpdate()->first();
        abort_if($order->status === 'COMPLETED', 422, 'A completed laboratory order cannot be reopened from this screen.');

        if (in_array($data['status'],['SAMPLE_COLLECTED','IN_PROGRESS'])) { app(\App\Services\LabBillingService::class)->requirePaid($order); }
        abort_if($order->status==='CANCELLED',422,'A cancelled order cannot be reopened.');
        abort_if($data['status']==='CANCELLED' && $invoice && ($invoice->paid_amount>0 || $invoice->insurance_covered>0 || $invoice->payments()->where('status','COMPLETED')->exists()),422,'Paid orders require a refund review before cancellation.');
        $order->update(['status'=>$data['status']]);
        if ($data['status'] === 'IN_PROGRESS') {
            $order->items()->where('status','PENDING')->update(['status'=>'IN_PROGRESS']);
        }
        if ($data['status'] === 'CANCELLED') {
            $order->items()->where('status','!=','COMPLETED')->update(['status'=>'CANCELLED']);
            $invoice?->update(['status'=>'VOID','balance_amount'=>0]);
        }
        return true;
        });

        if (!$changed) { return back()->with('success','Already updated. This order is already '.strtolower(str_replace('_',' ',$data['status'])).'.'); }
        $this->audit->record('lab.order_status_changed',$order,[],['status'=>$data['status']]);
        return back()->with('success','Laboratory order status updated.');
    }

    public function resultForm(LabOrderItem $item)
    {
        app(\App\Services\LabBillingService::class)->requirePaid($item->order);
        abort_if($item->status === 'CANCELLED', 422, 'A cancelled test cannot receive a result.');

        return view('lab.result',[
            'item' => $item->load('order.patient','order.doctor.user','test','result'),
            'attachments' => DB::table('clinical_attachments')
                ->where('lab_result_id',$item->result?->lab_result_id)
                ->latest('attachment_id')->get(),
        ]);
    }

    public function storeResult(LabResultRequest $request, LabOrderItem $item)
    {
        // Report images may require multiple AI requests; allow three minutes.
        set_time_limit(180);
        $item->loadMissing('order.patient.account','order.doctor.user','test','result');
        app(\App\Services\LabBillingService::class)->requirePaid($item->order);
        abort_if($item->status === 'CANCELLED', 422, 'A cancelled test cannot receive a result.');
        $data = $request->validated();

        $attachmentForAi = null;
        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $attachmentForAi = [
                'mime_type' => $file->getMimeType() ?: $file->getClientMimeType() ?: 'application/octet-stream',
                'data' => base64_encode($file->get()),
            ];
        }

        $resultText = trim((string) ($data['result_text'] ?? ''));
        $aiInput = $resultText !== ''
            ? $resultText
            : 'A protected laboratory report document was uploaded. Summarize only clearly visible report content and state when manual clinical review is required.';
        // Leave enough of the 180-second budget for persistence after provider retries.
        $timeouts=['services.gemini.timeout'=>config('services.gemini.timeout'),'services.anthropic.timeout'=>config('services.anthropic.timeout')];
        foreach($timeouts as $key=>$timeout)config([$key=>max(1,min(40,(int)$timeout))]);
        try {
            $summary = $this->ai->summarizeLabResult($item->test->test_name, $aiInput, $attachmentForAi);
        } catch (\Throwable $exception) {
            report($exception);
            // The original laboratory report remains usable without an AI explanation.
            $summary=[];
        } finally {
            config($timeouts);
        }

        $result = DB::transaction(function () use ($request,$item,$data,$summary,$resultText) {
            $lockedOrder=LabOrder::whereKey($item->lab_order_id)->lockForUpdate()->firstOrFail();
            app(\App\Services\LabBillingService::class)->requirePaid($lockedOrder);
            $release = $request->boolean('verify_now');
            $result = LabResult::updateOrCreate(
                ['lab_order_item_id'=>$item->lab_order_item_id],
                [
                    'result_text' => $resultText !== '' ? $resultText : null,
                    'abnormal_flag' => $request->boolean('abnormal_flag') || (bool) ($summary['abnormal_flag'] ?? false),
                    'ai_summary' => mb_substr((string) ($summary['summary'] ?? ''), 0, 30000) ?: null,
                    'result_json' => $summary,
                    'ai_provider' => $summary['_provider'] ?? null,
                    'ai_model' => $summary['_model'] ?? null,
                    'entered_by' => $request->user()->user_id,
                    'verified_by' => $release ? $request->user()->user_id : null,
                    'verified_at' => $release ? now() : null,
                    'released_to_patient_at' => $release ? now() : null,
                ]
            );

            if ($request->hasFile('report_file')) {
                $file = $request->file('report_file');
                $path = $file->store('', 'lab_private');
                if (!$path)throw \Illuminate\Validation\ValidationException::withMessages(['report_file'=>'The report could not be stored. Please try again.']);
                DB::table('clinical_attachments')->insert([
                    'patient_id' => $item->order->patient_id,
                    'lab_result_id' => $result->lab_result_id,
                    'uploaded_by' => $request->user()->user_id,
                    'original_name' => mb_substr($file->getClientOriginalName(),0,255),
                    'stored_path' => $path,
                    'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                    'file_size' => $file->getSize(),
                    'sha256' => hash_file('sha256',$file->getRealPath()),
                    'created_at' => now(),
                ]);
            }

            $item->update(['status'=>'COMPLETED']);
            if ($item->order->items()->where('status','!=','COMPLETED')->doesntExist()) {
                $item->order->update(['status'=>'COMPLETED','completed_at'=>now()]);
            } else {
                $item->order->update(['status'=>'IN_PROGRESS']);
            }

            return $result;
        });

        $doctorUserId = $item->order->doctor?->user_id;
        if ($doctorUserId) {
            $this->notifications->create(
                $doctorUserId,
                'laboratory_result',
                'Laboratory result completed',
                "{$item->test->test_name} for patient {$item->order->patient->patient_code} is ready for clinical review.",
                'LabResult',
                $result->lab_result_id
            );
        }

        if ($request->boolean('verify_now')) {
            $this->notifications->create(
                $item->order->patient->account_user_id,
                'laboratory_result',
                'Laboratory report available',
                "Your verified {$item->test->test_name} report is available securely in MEDICARE.",
                'LabResult',
                $result->lab_result_id
            );
            $this->mail->labResultReady($item);
        }

        $this->audit->record('lab.result_saved',$result,[],[
            'released_to_patient' => $request->boolean('verify_now'),
            'attachment_uploaded' => $request->hasFile('report_file'),
            'ai_provider' => $summary['_provider'] ?? null,
        ]);

        return redirect()->route('lab.orders')->with(
            'success',
            $request->boolean('verify_now')
                ? 'Result saved, doctor notified and patient securely notified by portal/email.'
                : 'Result saved as a clinical draft and the ordering doctor was notified.'
        );
    }
}
