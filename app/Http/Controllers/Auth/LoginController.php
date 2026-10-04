<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            // 1. Сначала переносим товары из сессии в базу для этого юзера
            app(CartService::class)->mergeAfterLogin();

            // 2. Затем обновляем ID сессии (защита от фиксации сессии)
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль'
        ]);
    }

    public function logout(Request $request)
    {
        $previousUrl = url()->previous();
        $previousHost = parse_url($previousUrl, PHP_URL_HOST);
        $currentHost = $request->getHost();

        if ($previousHost !== null && strcasecmp($previousHost, $currentHost) !== 0) {
            $previousUrl = url('/');
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->to($previousUrl);
    }
}
