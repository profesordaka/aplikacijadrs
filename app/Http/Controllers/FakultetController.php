<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

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

        'univerzitet_naziv' => 'nullable|string|max:255', // ime univerziteta iz inputa
    ], [
        'email.unique' => 'Fakultet sa ovim emailom već postoji.',
    ]);

    $fakultet = new Fakultet();
    $fakultet->naziv = $validated['naziv'];
    $fakultet->email = $validated['email'];
    $fakultet->telefon = $validated['telefon'];
    $fakultet->web = $validated['web'] ?? null;


   // Ako korisnik unese novi univerzitet
if ($request->filled('new_univerzitet')) {
    $univerzitet = Univerzitet::firstOrCreate([
        'naziv' => $request->new_univerzitet
    ]);
    $fakultet->univerzitet_id = $univerzitet->id;
} else {
    // Ako je select popunjen, uzmi ID iz selecta
    $fakultet->univerzitet_id = $request->univerzitet_id ?: null;
}

    $fakultet->save();

    return redirect()->back()->with('success', 'Fakultet uspješno dodat!');
}

public function update(Request $request, $id)
{
    $fakultet = Fakultet::findOrFail($id);

    $validated = $request->validate([
        'naziv' => 'required|string|max:255',
        'email' => ['required','email','max:255', Rule::unique('fakulteti')->ignore($fakultet->id)],
        'telefon' => 'required|string|max:255',
        'web' => 'nullable|string|max:255',

        'univerzitet_naziv' => 'nullable|string|max:255',
        'file' => 'nullable|file|max:10240', // 10MB max
    ], [
        'email.unique' => 'Fakultet sa ovim emailom već postoji.',
    ]);

    $fakultet->naziv = $validated['naziv'];
    $fakultet->email = $validated['email'];
    $fakultet->telefon = $validated['telefon'];
    $fakultet->web = $validated['web'] ?? null;


    if ($request->hasFile('file')) {
        if ($fakultet->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($fakultet->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($fakultet->file_path);
        }
        $path = $request->file('file')->store('fakulteti_files', 'public');
        $fakultet->file_path = $path;
    }

    // Ako korisnik unese novi univerzitet
if ($request->filled('new_univerzitet')) {
    $univerzitet = Univerzitet::firstOrCreate([
        'naziv' => $request->new_univerzitet
    ]);
    $fakultet->univerzitet_id = $univerzitet->id;
} else {
    // Ako je select popunjen, uzmi ID iz selecta
    $fakultet->univerzitet_id = $request->univerzitet_id ?: null;
}

    $fakultet->save();

    return redirect()->route('fakulteti.index')->with('success', 'Fakultet uspješno ažuriran!');
}


    public function destroy($id)
    {
        $fakultet = Fakultet::findOrFail($id);
        if ($fakultet->file_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($fakultet->file_path)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($fakultet->file_path);
        }
        $fakultet->delete();

        return redirect()->route('fakulteti.index')->with('success', 'Fakultet uspješno obrisan!');
    }

    public function downloadFile($id)
    {
        $fakultet = Fakultet::findOrFail($id);
        if (!$fakultet->file_path || !\Illuminate\Support\Facades\Storage::disk('public')->exists($fakultet->file_path)) {
            return redirect()->back()->with('error', 'Fajl ne postoji.');
        }
        return \Illuminate\Support\Facades\Storage::disk('public')->download($fakultet->file_path);
    }
}
