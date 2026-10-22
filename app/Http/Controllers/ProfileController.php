<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
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
        $user = Auth::user();
        $data = $request->validate([
            'name'  => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female',
            'birth_date' => 'nullable|date|before_or_equal:today',
            'avatar' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('avatar')) {

            // удалить старый файл
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }
        $user->update($data);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Данные успешно обновлены']);
        }

        return back()->with('success', 'Профиль обновлён');
    }

    public function updatePhone(Request $request)
    {
        $data = $request->validate([
            'phone' => ['required', 'regex:/^\+375 \(\d{2}\) \d{3}-\d{2}-\d{2}$/'],
        ]);

        Auth::user()->update(['phone' => $data['phone']]);

        return back()->with('success', 'Номер телефона успешно обновлён.');
    }

    public function updateEmail(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/u',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update(['email' => $data['email']]);

        return back()->with('success', 'Email успешно обновлён.');
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

    public function reviews()
    {
        return view('profile.reviews', [
            'reviews' => auth()->user()->reviews()->latest()->get()
        ]);
    }

    public function order(Order $order)
    {
        $order->load('items.variant');

        $variantIds = $order->items
            ->pluck('product_variant_id')
            ->filter()
            ->unique();

        $reviewsByVariant = auth()->user()
            ->reviews()
            ->whereIn('product_variant_id', $variantIds)
            ->get()
            ->keyBy('product_variant_id');

        return view('profile.order', compact('order', 'reviewsByVariant'));
    }
}
