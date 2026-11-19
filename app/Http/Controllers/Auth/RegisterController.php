<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function showRegistrationForm()
    {
        return redirect()->route('home')->with('auth_modal', 'register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'phone' => 'required|string|max:50',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'last_name' => $data['last_name'] ?? null,
            'gender' => $data['gender'],
            'birth_date' => $data['birth_date'] ?? null,
            'phone' => $data['phone'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);
        $request->session()->regenerate();

        $returnUrl = (string) $request->input('_auth_return_to', route('home'));
        $parts = parse_url($returnUrl);
        if (
            !str_starts_with($returnUrl, '/')
            || str_starts_with($returnUrl, '//')
            || str_starts_with($returnUrl, '/\\')
            || isset($parts['scheme'])
            || isset($parts['host'])
        ) {
            $returnUrl = route('home');
        }

        $request->session()->put('url.intended', $returnUrl);

        return redirect()->to($returnUrl)->with('auth_modal', 'verify-email');
    }

}
