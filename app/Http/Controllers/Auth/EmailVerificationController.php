<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        return redirect()->route('home')->with('auth_modal', 'verify-email');
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        return redirect()->intended('/');
    }

    public function send(Request $request)
    {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('auth_modal', 'verify-email')->with('success', 'Письмо отправлено');
    }
}
