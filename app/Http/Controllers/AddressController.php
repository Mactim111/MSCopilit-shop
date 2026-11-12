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
        $addressLine = $this->formattedAddressLine($data);

        DB::transaction(function () use ($data, $userId, $addressLine): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $isDefault = (bool) $data['is_default'];

            if ($isDefault) {
                $user->addresses()->update(['is_default' => false]);
            }

            $user->addresses()->create([
                ...$data,
                'address_line' => $addressLine,
                'is_default' => $isDefault,
            ]);
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно обновлены');
    }

    public function update(Request $request, Address $address): JsonResponse|RedirectResponse
    {
        $data = $this->validatedAddress($request);
        $userId = $request->user()->id;
        $addressLine = $this->formattedAddressLine($data);

        DB::transaction(function () use ($data, $userId, $address, $addressLine): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $address = $user->addresses()->lockForUpdate()->findOrFail($address->id);
            $isDefault = (bool) $data['is_default'];

            if ($isDefault) {
                $user->addresses()
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }

            $address->update([
                ...$data,
                'address_line' => $addressLine,
                'is_default' => $isDefault,
            ]);
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно обновлены');
    }

    public function setDefault(Request $request, Address $address): JsonResponse|RedirectResponse
    {
        $userId = $request->user()->id;

        DB::transaction(function () use ($userId, $address): void {
            $user = User::query()->lockForUpdate()->findOrFail($userId);
            $address = $user->addresses()->lockForUpdate()->findOrFail($address->id);

            $user->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
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
        });

        return $this->respondWithUpdatedAddresses($request, 'Данные успешно обновлены');
    }

    private function validatedAddress(Request $request): array
    {
        return $request->validate([
            'city' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'house' => ['required', 'string', 'max:255'],
            'apartment' => ['nullable', 'string', 'max:255'],
            'entrance' => ['nullable', 'string', 'max:255'],
            'floor' => ['nullable', 'string', 'max:255'],
            'is_default' => ['required', 'boolean'],
        ]);
    }

    private function formattedAddressLine(array $data): string
    {
        return (new Address($data))->formattedAddressLine();
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
