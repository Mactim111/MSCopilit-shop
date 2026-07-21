<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\Order;

class ProfileController extends Controller
{
    public function index()
    {
        return view('profile.index', [
            'user' => Auth::user()
        ]);
    }

    public function updateProfile(Request $request)
    {
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $user = Auth::user();
        if ($request->hasFile('avatar')) {

            // удалить старый файл
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);

        return back()->with('success', 'Профиль обновлён');
    }

    public function updatePassword(Request $request)
    {
        $data = $request->validate([
            'current_password'      => 'required',
            'password'              => 'required|min:6|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Текущий пароль неверный']);
        }

        $user->password = Hash::make($data['password']);
        $user->save();

        return back()->with('success', 'Пароль обновлён');
    }

    public function orders()
    {
        return view('profile.orders', [
            'orders' => auth()->user()->orders()->latest()->get()
        ]);
    }

    public function order(Order $order)
    {
        $order->load('items.product');
        return view('profile.order', compact('order'));
    }
}
