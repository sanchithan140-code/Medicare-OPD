<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use App\Models\Specialty;
use App\Models\WeeklyDiseaseTrend;
use App\Services\AI\AiGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PublicController extends Controller
{
    public function __construct(private readonly AiGateway $ai) {}

    public function home()
    {
        return view('public.home', [
            'specialties' => Specialty::where('active', true)->orderBy('specialty_name')->limit(8)->get(),
            'doctors' => Doctor::with('user', 'specialty')->where('active', true)->limit(4)->get(),
            'trends' => WeeklyDiseaseTrend::with('condition')->latest('week_start')->limit(4)->get(),
        ]);
    }

    public function chatbot(Request $request): JsonResponse
    {
        $data = $request->validate([
            'messages' => ['required', 'array', 'min:1', 'max:10'],
            'messages.*.role' => ['required', 'in:user,assistant'],
            'messages.*.content' => ['required', 'string', 'max:1000'],
        ]);

        $result = $this->ai->chat($data['messages']);

        return response()->json([
            'reply' => $result['reply'],
            'ignored' => $result['ignored'],
            'provider' => $result['_provider'] ?? null,
        ]);
    }
}
