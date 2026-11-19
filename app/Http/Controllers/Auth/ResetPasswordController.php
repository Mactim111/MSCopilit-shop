<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;

class ResetPasswordController extends Controller
{
    public function showResetForm(Request $request, $token)
    {
        return redirect()->route('home')->with([
            'auth_modal' => 'reset-password',
            'auth_modal_token' => $token,
            'auth_modal_email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset($data, function ($user, $password) {
            $user->password = Hash::make($password);
            $user->save();
        });

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('home')->with('auth_modal', 'login')->with('success', 'Пароль обновлён')
            : back()->with('auth_modal', 'reset-password')
                ->withErrors(['email' => 'Ошибка восстановления'])
                ->withInput($request->except(['password', 'password_confirmation']));
    }
}
