<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class PendingPatientLink
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->requested_patient_code !== null) {
            return response()->view('patient.link-pending')->header('Cache-Control','private, no-store');
        }
        return $next($request);
    }
}
