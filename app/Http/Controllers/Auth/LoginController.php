<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm(Request $request)
    {
        $previousUrl = url()->previous();
        $previousHost = parse_url($previousUrl, PHP_URL_HOST);
        $previousPath = parse_url($previousUrl, PHP_URL_PATH);
        $loginPath = parse_url(route('login'), PHP_URL_PATH);
        $previousPathNormalized = rtrim((string) $previousPath, '/');
        $isAuthPage = in_array($previousPathNormalized, [
            '/login',
            '/register',
            '/forgot-password',
            '/email/verify',
        ], true) || str_starts_with($previousPathNormalized, '/reset-password/');

        if (
            $previousHost !== null
            && strcasecmp($previousHost, $request->getHost()) === 0
            && $previousPath !== null
            && rtrim($previousPath, '/') !== rtrim($loginPath, '/')
            && !$isAuthPage
        ) {
            $query = parse_url($previousUrl, PHP_URL_QUERY);
            $intendedUrl = $previousPath . ($query ? "?{$query}" : '');
            $request->session()->put('url.intended', $intendedUrl);
        }

        return redirect()
            ->to($intendedUrl ?? route('home'))
            ->with('auth_modal', 'login');
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
            return redirect()->intended($this->localReturnUrl($request));
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль'
        ])->withInput($request->except('password'))->with('auth_modal', 'login');
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

    private function localReturnUrl(Request $request): string
    {
        $returnUrl = (string) $request->input('_auth_return_to', route('home'));
        $parts = parse_url($returnUrl);

        if (
            !str_starts_with($returnUrl, '/')
            || str_starts_with($returnUrl, '//')
            || str_starts_with($returnUrl, '/\\')
            || isset($parts['scheme'])
            || isset($parts['host'])
        ) {
            return route('home');
        }

        return $returnUrl;
    }
}
