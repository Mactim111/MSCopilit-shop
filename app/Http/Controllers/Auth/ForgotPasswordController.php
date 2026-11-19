<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    public function showLinkRequestForm()
    {
        return redirect()->route('home')->with('auth_modal', 'forgot-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('auth_modal', 'forgot-password')->with('success', 'Ссылка отправлена на email')
            : back()->with('auth_modal', 'forgot-password')->withErrors(['email' => 'Не удалось отправить ссылку']);
    }
}
