<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountUpdateRequest;
use App\Models\Notification;
use App\Services\AuditService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function __construct(private readonly AuditService $audit) {}

    public function edit()
    {
        return view('account.edit');
    }


    public function notifications()
    {
        Notification::where('user_id',Auth::id())->whereNull('read_at')->update(['read_at'=>now()]);
        return view('account.notifications', [
            'notifications' => Notification::where('user_id',Auth::id())->latest('notification_id')->paginate(25),
        ]);
    }

    public function update(AccountUpdateRequest $request)
    {
        $user = $request->user();
        $data = $request->validated();
        $before = $user->only(['full_name','email','phone']);

        $changes = [
            'full_name' => $data['full_name'],
            'phone' => $data['phone'],
        ];

        if (! empty($data['password'])) {
            $changes['password_hash'] = Hash::make($data['password']);
            $changes['password_changed_at'] = now();
        }

        $user->update($changes);
        $this->audit->record('account.updated', $user, $before, $user->only(['full_name','email','phone']));

        return back()->with('success', 'Account settings updated successfully.');
    }
}
