<?php

namespace App\Http\Controllers;

use App\Models\Address;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $data = $this->validatedAddress($request);
        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $isFirstAddress = !$user->addresses()->exists();
            $isDefault = $isFirstAddress || (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([
                ...$data,
                'is_default' => $isDefault,
            ]);
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно обновлены');
    }

    public function update(Request $request, Address $address): JsonResponse|RedirectResponse
    {
        $data = $this->validatedAddress($request);
        $userId = $request->user()->id;

        DB::transaction(function () use ($data, $userId, $address): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $address = $user->addresses()->lockForUpdate()->findOrFail($address->id);
            $isDefault = array_key_exists('is_default', $data)
                ? (bool) $data['is_default']
                : $address->is_default;

            if ($isDefault) {
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                ...$data,
                'is_default' => $isDefault,
            ]);
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно обновлены');
    }

    public function setDefault(Request $request, Address $address): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'is_default' => ['required', 'boolean'],
        ]);
        $userId = $request->user()->id;
        $isDefault = (bool) $data['is_default'];

        DB::transaction(function () use ($userId, $address, $isDefault): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $address = $user->addresses()->lockForUpdate()->findOrFail($address->id);

            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $address->update(['is_default' => $isDefault]);
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно обновлены');
    }

    public function destroy(Request $request, Address $address): JsonResponse|RedirectResponse
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($userId, $address): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $address = $user->addresses()->lockForUpdate()->findOrFail($address->id);
            $address->delete();

            $remainingAddresses = $user->addresses()->lockForUpdate()->get();

            if ($remainingAddresses->count() === 1) {
                $remainingAddresses->first()->update(['is_default' => true]);
            }
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно изменены');
    }

    private function validatedAddress(Request $request): array
    {
        return $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'address_line' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'zip' => ['nullable', 'string', 'max:50'],
            'country' => ['required', 'string', 'max:255'],
            'is_default' => ['sometimes', 'boolean'],
        ]);
    }

    private function respondWithUpdatedAddresses(
        Request $request,
        string $message
    ): JsonResponse|RedirectResponse {
        if ($request->expectsJson()) {
            $addresses = $request->user()
                ->addresses()
                ->orderByDesc('is_default')
                ->latest('id')
                ->get();

            return response()->json([
                'message' => $message,
                'default_address_id' => $addresses->firstWhere('is_default', true)?->id,
                'addresses_html' => view('profile.partials.addresses-list', compact('addresses'))->render(),
            ]);
        }

        return redirect()
            ->route('profile.addresses')
            ->with('success', $message);
    }
}
