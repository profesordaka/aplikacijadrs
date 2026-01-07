<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Validation\Rule;

class FakultetController extends Controller
{
    public function index()
    {
        $fakulteti = Fakultet::with('univerzitet')->get();
        $univerziteti = Univerzitet::all();
        return view('fakultet.index', compact('fakulteti', 'univerziteti'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'naziv' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:fakulteti,email',
            'telefon' => 'required|string|max:255',
            'web' => 'nullable|string|max:255',
            'uputstvo_za_ocjene' => 'nullable|string',
            'univerzitet_id' => 'required|exists:univerziteti,id',
        ]);

        if ($request->hasFile('uputstvo_file')) {
            $path = $request->file('uputstvo_file')->store('fakulteti', 'public');
            $validated['uputstvo_file'] = $path;
        }

        Fakultet::create($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno dodat!');
    }

    public function update(Request $request, $id)
    {
        $fakultet = Fakultet::findOrFail($id);

        $validated = $request->validate([
            'naziv' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('fakulteti')->ignore($fakultet->id),
            ],
            'telefon' => 'required|string|max:255',
            'web' => 'nullable|string|max:255',
            'uputstvo_za_ocjene' => 'nullable|string',
            'univerzitet_id' => 'required|exists:univerziteti,id',
        ]);

        if ($request->hasFile('uputstvo_file')) {
            $path = $request->file('uputstvo_file')->store('fakulteti', 'public');
            $validated['uputstvo_file'] = $path;
        }

        $fakultet->update($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno ažuriran!');
    }

    public function destroy($id)
    {
        $fakultet = Fakultet::findOrFail($id);
        $fakultet->delete();

        return redirect()->back()->with('success', 'Fakultet uspješno obrisan!');
    }
}
