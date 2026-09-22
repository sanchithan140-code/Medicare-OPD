<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SecureDocumentController extends Controller
{
    private function reportDisk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('lab_private');
        return $disk;
    }

    public function download(Request $request, int $attachment): StreamedResponse
    {
        $row = $this->authorizedAttachment($request, $attachment);

        return $this->reportDisk()->download($row->stored_path, $row->original_name, [
            'Content-Type' => $row->mime_type,
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    public function view(Request $request, int $attachment): StreamedResponse
    {
        $row = $this->authorizedAttachment($request, $attachment);

        return $this->reportDisk()->response($row->stored_path, $row->original_name, [
            'Content-Type' => $row->mime_type,
            'Content-Disposition' => 'inline; filename="'.addslashes($row->original_name).'"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store, max-age=0',
        ]);
    }

    private function authorizedAttachment(Request $request, int $attachment): object
    {
        $row = DB::table('clinical_attachments as ca')
            ->leftJoin('lab_results as lr','lr.lab_result_id','=','ca.lab_result_id')
            ->leftJoin('lab_order_items as loi','loi.lab_order_item_id','=','lr.lab_order_item_id')
            ->leftJoin('lab_orders as lo','lo.lab_order_id','=','loi.lab_order_id')
            ->leftJoin('doctors as d','d.doctor_id','=','lo.ordered_by_doctor_id')
            ->select('ca.*','lr.released_to_patient_at','lo.ordered_by_doctor_id','d.user_id as doctor_user_id')
            ->where('ca.attachment_id',$attachment)
            ->first();

        abort_unless($row, 404);
        abort_unless($this->reportDisk()->exists($row->stored_path), 404);

        $user = $request->user();
        $role = $user->role?->role_name;
        $allowed = in_array($role, ['lab_officer','admin','main_admin'], true)
            || ($role === 'doctor' && $user->account_status === 'ACTIVE'
                && DB::table('doctors')->where('user_id',$user->user_id)->where('active',1)->exists())
            || ($role === 'patient'
                && $row->released_to_patient_at
                && DB::table('patients')->where('patient_id',$row->patient_id)->where('account_user_id',$user->user_id)->exists());

        abort_unless($allowed, 403);

        return $row;
    }
}
