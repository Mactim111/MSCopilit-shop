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
        $previousPathNormalized = rtrim((string) $previousPath, '/');
        $isAuthPage = in_array($previousPathNormalized, [
            '/login',
            '/register',
            '/forgot-password',
            '/email/verify',
        ], true) || str_starts_with($previousPathNormalized, '/reset-password/');
        $modalReturnUrl = null;

        if (
            $previousHost !== null
            && strcasecmp($previousHost, $request->getHost()) === 0
            && $previousPath !== null
            && !$isAuthPage
        ) {
            $query = parse_url($previousUrl, PHP_URL_QUERY);
            $modalReturnUrl = $previousPath . ($query ? "?{$query}" : '');
            $request->session()->put('url.intended', $modalReturnUrl);
        } else {
            $intendedUrl = $request->session()->get('url.intended');
            $intendedParts = is_string($intendedUrl) ? parse_url($intendedUrl) : false;
            $intendedHost = is_array($intendedParts) ? ($intendedParts['host'] ?? null) : null;
            $intendedPath = is_array($intendedParts) ? ($intendedParts['path'] ?? null) : null;

            if (
                $intendedPath !== null
                && str_starts_with($intendedPath, '/')
                && !str_starts_with($intendedPath, '//')
                && ($intendedHost === null || strcasecmp($intendedHost, $request->getHost()) === 0)
                && $this->isProtectedAreaPath($intendedPath)
            ) {
                $modalReturnUrl = $intendedPath;
                if (!empty($intendedParts['query'])) {
                    $modalReturnUrl .= '?' . $intendedParts['query'];
                }
            }
        }

        $modalReturnUrl ??= route('home');

        if ($this->isProtectedAreaPath((string) parse_url($modalReturnUrl, PHP_URL_PATH))) {
            return redirect()->route('home')->with([
                'auth_modal' => 'login',
                'auth_return_to' => $modalReturnUrl,
            ]);
        }

        return redirect()->to($modalReturnUrl)->with('auth_modal', 'login');
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
            $returnUrl = $this->localReturnUrl($request);

            if ($request->has('_auth_return_to')) {
                $request->session()->forget('url.intended');

                return redirect()->to($returnUrl);
            }

            return redirect()->intended($returnUrl);
        }

        return back()->withErrors([
            'email' => 'Неверный email или пароль'
        ])->withInput($request->except('password'))->with('auth_modal', 'login');
    }

    public function logout(Request $request)
    {
        $previousUrl = url()->previous();
        $previousParts = parse_url($previousUrl);
        $previousHost = $previousParts['host'] ?? null;
        $previousPath = $previousParts['path'] ?? null;
        $isProtectedArea = $this->isProtectedAreaPath($previousPath);

        if (
            ($previousHost !== null && strcasecmp($previousHost, $request->getHost()) !== 0)
            || $previousPath === null
            || !str_starts_with($previousPath, '/')
            || str_starts_with($previousPath, '//')
            || $isProtectedArea
        ) {
            $previousUrl = route('home');
        } else {
            $previousUrl = $previousPath;
            if (!empty($previousParts['query'])) {
                $previousUrl .= '?' . $previousParts['query'];
            }
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

    private function isProtectedAreaPath(?string $path): bool
    {
        return in_array($path, ['/profile', '/admin'], true)
            || ($path !== null && (
                str_starts_with($path, '/profile/')
                || str_starts_with($path, '/admin/')
            ));
    }
}
