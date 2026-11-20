<?php

namespace App\Http\Controllers;

use App\Mail\EmailChangeCode;
use App\Models\Address;
use App\Models\EmailChangeVerification;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        return view('profile.index', [
            'user' => $user,
            'emailChangeVerification' => EmailChangeVerification::query()
                ->where('user_id', $user->id)
                ->first(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        $data = $request->validate([
            'name'  => 'required|string|max:60',
            'last_name' => 'nullable|string|max:60',
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
        $request->merge([
            'email' => mb_strtolower(trim((string) $request->input('email'))),
        ]);
        $validator = Validator::make($request->all(), [
            'email' => [
                'required',
                'string',
                'email',
                'regex:/^[^@\s]+@[^@\s]+\.[^@\s]+$/u',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ], [
            'email.required' => 'Введите новый email.',
            'email.email' => 'Введите корректный email.',
            'email.regex' => 'Введите корректный email.',
            'email.max' => 'Email не должен превышать 255 символов.',
            'email.unique' => 'Email уже занят',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('email_change_step', 'email');
        }

        $email = trim($validator->validated()['email']);
        $emailIsAlreadyUsed = User::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->where('id', '!=', $user->id)
            ->exists();
        if ($emailIsAlreadyUsed) {
            return back()
                ->withErrors(['email' => 'Email уже занят'])
                ->withInput()
                ->with('email_change_step', 'email');
        }

        if (strcasecmp($email, $user->email) === 0) {
            return back()
                ->withErrors(['email' => 'Этот email уже привязан к аккаунту.'])
                ->withInput()
                ->with('email_change_step', 'email');
        }

        $code = '';
        $verification = DB::transaction(function () use ($user, $email, &$code): EmailChangeVerification|false {
            $existing = EmailChangeVerification::query()
                ->where('user_id', $user->id)
                ->lockForUpdate()
                ->first();

            if ($existing && $existing->resend_available_at->isFuture()) {
                return false;
            }

            $code = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $verification = EmailChangeVerification::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'email' => $email,
                    'code_hash' => Hash::make($code),
                    'expires_at' => now()->addMinutes(10),
                    'resend_available_at' => now()->addSeconds(60),
                    'attempts' => 0,
                ]
            );

            return $verification;
        });

        if ($verification === false) {
            return back()
                ->withErrors(['email' => 'Новый код можно запросить не чаще одного раза в 60 секунд.'])
                ->withInput()
                ->with('email_change_step', 'email');
        }

        Mail::to($email)->send(new EmailChangeCode($code));

        return back()->with('email_change_step', 'code');
    }

    public function verifyEmailChange(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => ['required', 'regex:/^\d{4}$/'],
        ], [
            'code.required' => 'Введите код из письма.',
            'code.regex' => 'Код должен состоять из 4 цифр.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('email_change_step', 'code');
        }

        $userId = Auth::id();
        $code = $validator->validated()['code'];
        $result = DB::transaction(function () use ($userId, $code): array {
            $verification = EmailChangeVerification::query()
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            if (!$verification || $verification->expires_at->isPast()) {
                return ['error' => 'Срок действия кода истёк. Запросите новый код.'];
            }

            if ($verification->attempts >= 5) {
                return ['error' => 'Вы исчерпали лимит попыток верификации.'];
            }

            if (!Hash::check($code, $verification->code_hash)) {
                $verification->increment('attempts');
                $remainingAttempts = 5 - $verification->attempts;

                return [
                    'error' => $remainingAttempts > 0
                        ? "Код не совпал. Осталось попыток: {$remainingAttempts}"
                        : 'Вы исчерпали лимит попыток верификации.',
                ];
            }

            $user = User::query()->whereKey($userId)->lockForUpdate()->firstOrFail();
            $emailIsTaken = User::query()
                ->whereRaw('LOWER(email) = ?', [mb_strtolower($verification->email)])
                ->where('id', '!=', $userId)
                ->exists();

            if ($emailIsTaken) {
                $verification->delete();

                return ['email_error' => 'Email уже занят', 'email' => $verification->email];
            }

            $user->forceFill([
                'email' => $verification->email,
                'email_verified_at' => now(),
            ])->save();
            $verification->delete();

            return ['success' => true];
        });

        if (isset($result['email_error'])) {
            return back()
                ->withErrors(['email' => $result['email_error']])
                ->withInput(['email' => $result['email']])
                ->with('email_change_step', 'email');
        }

        if (isset($result['error'])) {
            return back()
                ->withErrors(['code' => $result['error']])
                ->withInput(['code' => $code])
                ->with('email_change_step', 'code');
        }

        return back()->with('email_change_success', true);
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

    public function orders(Request $request)
    {
        $data = $request->validate([
            'filter' => ['sometimes', Rule::in([
                'all',
                Order::ORDER_COMPLETED,
                Order::ORDER_CANCELLED,
                Order::ORDER_CREATED,
                Order::ORDER_PROCESSING,
                Order::ORDER_READY_FOR_PICKUP,
                Order::ORDER_IN_TRANSIT,
                Order::ORDER_CANCELLATION,
            ])],
        ]);
        $filter = $data['filter'] ?? 'all';
        $userOrders = $request->user()->orders();

        $orderCounts = [
            'all' => (clone $userOrders)->count(),
            Order::ORDER_COMPLETED => (clone $userOrders)
                ->where('status', Order::STATUS_COMPLETED)
                ->count(),
            Order::ORDER_CANCELLED => (clone $userOrders)
                ->where('status', Order::STATUS_CANCELLED)
                ->count(),
            Order::ORDER_CREATED => (clone $userOrders)
                ->where('status', Order::STATUS_NEW)
                ->whereNull('cancellation_requested_at')
                ->count(),
            Order::ORDER_PROCESSING => (clone $userOrders)
                ->where('status', Order::STATUS_PROCESSING)
                ->whereNull('cancellation_requested_at')
                ->count(),
            Order::ORDER_READY_FOR_PICKUP => (clone $userOrders)
                ->where('status', Order::STATUS_READY_FOR_PICKUP)
                ->where('delivery_method', Order::DELIVERY_PICKUP)
                ->whereNull('cancellation_requested_at')
                ->count(),
            Order::ORDER_IN_TRANSIT => (clone $userOrders)
                ->where(function ($query): void {
                    $query->where('status', Order::STATUS_IN_TRANSIT)
                        ->orWhere(function ($legacyQuery): void {
                            $legacyQuery->where('status', Order::STATUS_READY_FOR_PICKUP)
                                ->where('delivery_method', Order::DELIVERY_COURIER);
                        });
                })
                ->whereNull('cancellation_requested_at')
                ->count(),
            Order::ORDER_CANCELLATION => (clone $userOrders)
                ->whereIn('status', [
                    Order::STATUS_NEW,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_READY_FOR_PICKUP,
                    Order::STATUS_IN_TRANSIT,
                ])
                ->whereNotNull('cancellation_requested_at')
                ->count(),
        ];

        $ordersQuery = $userOrders->with(['items.variant.images']);
        switch ($filter) {
            case Order::ORDER_COMPLETED:
                $ordersQuery->where('status', Order::STATUS_COMPLETED);
                break;
            case Order::ORDER_CANCELLED:
                $ordersQuery->where('status', Order::STATUS_CANCELLED);
                break;
            case Order::ORDER_CREATED:
                $ordersQuery->where('status', Order::STATUS_NEW)
                    ->whereNull('cancellation_requested_at');
                break;
            case Order::ORDER_PROCESSING:
                $ordersQuery->where('status', Order::STATUS_PROCESSING)
                    ->whereNull('cancellation_requested_at');
                break;
            case Order::ORDER_READY_FOR_PICKUP:
                $ordersQuery->where('status', Order::STATUS_READY_FOR_PICKUP)
                    ->where('delivery_method', Order::DELIVERY_PICKUP)
                    ->whereNull('cancellation_requested_at');
                break;
            case Order::ORDER_IN_TRANSIT:
                $ordersQuery->where(function ($query): void {
                    $query->where('status', Order::STATUS_IN_TRANSIT)
                        ->orWhere(function ($legacyQuery): void {
                            $legacyQuery->where('status', Order::STATUS_READY_FOR_PICKUP)
                                ->where('delivery_method', Order::DELIVERY_COURIER);
                        });
                })
                    ->whereNull('cancellation_requested_at');
                break;
            case Order::ORDER_CANCELLATION:
                $ordersQuery->whereIn('status', [
                    Order::STATUS_NEW,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_READY_FOR_PICKUP,
                    Order::STATUS_IN_TRANSIT,
                ])
                    ->whereNotNull('cancellation_requested_at');
                break;
        }

        $orders = $ordersQuery
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('profile.orders', compact('orders', 'orderCounts', 'filter'));
    }

    public function requestOrderCancellation(Request $request, Order $order): JsonResponse|\Illuminate\Http\RedirectResponse
    {
        $requested = DB::transaction(function () use ($request, $order): bool {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->where('user_id', $request->user()->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                in_array($lockedOrder->status, [
                    Order::STATUS_NEW,
                    Order::STATUS_PROCESSING,
                    Order::STATUS_READY_FOR_PICKUP,
                    Order::STATUS_IN_TRANSIT,
                ], true),
                422,
                'Для завершённого или уже отменённого заказа нельзя запросить отмену.'
            );

            if ($lockedOrder->cancellation_requested_at !== null) {
                return false;
            }

            $lockedOrder->cancellation_requested_at = now();
            $lockedOrder->save();

            return true;
        });

        $message = $requested
            ? 'Запрос на отмену заказа отправлен.'
            : 'Запрос на отмену этого заказа уже отправлен.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'order_id' => $order->id,
                'profile_status' => Order::ORDER_CANCELLATION,
            ]);
        }

        return back()->with('success', $message);
    }

    public function reviews()
    {
        $data = request()->validate([
            'filter' => ['sometimes', Rule::in(['all', 'approved', 'rejected'])],
        ]);
        $filter = $data['filter'] ?? 'all';
        $userReviews = Auth::user()->reviews();

        $reviewCounts = [
            'all' => (clone $userReviews)->count(),
            'approved' => (clone $userReviews)
                ->where('moderation_status', Review::MODERATION_APPROVED)
                ->count(),
            'rejected' => (clone $userReviews)
                ->where('moderation_status', Review::MODERATION_REJECTED)
                ->count(),
        ];

        $reviewsQuery = $userReviews
            ->with(['variant.images', 'images', 'reply.admin'])
            ->withCount([
                'votes as likes_count' => fn ($votes) => $votes->where('type', 'like'),
                'votes as dislikes_count' => fn ($votes) => $votes->where('type', 'dislike'),
            ]);
        if ($filter !== 'all') {
            $status = $filter === 'approved'
                ? Review::MODERATION_APPROVED
                : Review::MODERATION_REJECTED;
            $reviewsQuery->where('moderation_status', $status);
        }

        $reviews = $reviewsQuery->latest()->get();

        if (request()->expectsJson()) {
            return response()->json([
                'html' => view('profile.partials.reviews-list', compact('reviews', 'filter'))->render(),
                'counts' => $reviewCounts,
                'filter' => $filter,
            ]);
        }

        return view('profile.reviews', compact('reviews', 'reviewCounts', 'filter'));
    }

    public function order(Order $order)
    {
        $order->load('items.variant.images');

        $variantIds = $order->items
            ->pluck('product_variant_id')
            ->filter()
            ->unique();

        $reviewsByVariant = Auth::user()
            ->reviews()
            ->whereIn('product_variant_id', $variantIds)
            ->get()
            ->keyBy('product_variant_id');

        return view('profile.order', compact('order', 'reviewsByVariant'));
    }

    public function addresses()
    {
        $addresses = Auth::user()
            ->addresses()
            ->orderByDesc('is_default')
            ->latest('id')
            ->get();

        return view('profile.addresses', compact('addresses'));
    }
}
