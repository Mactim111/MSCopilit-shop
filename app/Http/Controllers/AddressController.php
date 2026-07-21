<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    public function edit(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        return view('profile.address_edit', [
            'address' => $address
        ]);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $data = $request->validate([
            'label'        => 'nullable|string|max:255',
            'address_line' => 'required|string|max:255',
            'city'         => 'required|string|max:255',
            'state'        => 'nullable|string|max:255',
            'zip'          => 'nullable|string|max:50',
            'country'      => 'required|string|max:255',
        ]);

        $address->update($data);

        return redirect()->route('profile')->with('success', 'Адрес обновлён');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'label'        => 'nullable|string|max:255',
            'address_line' => 'required|string|max:255',
            'city'         => 'required|string|max:255',
            'state'        => 'nullable|string|max:255',
            'zip'          => 'nullable|string|max:50',
            'country'      => 'required|string|max:255',
        ]);

        Auth::user()->addresses()->create($data);

        return back()->with('success', 'Адрес добавлен');
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $address->delete();

        return back()->with('success', 'Адрес удалён');
    }
}
