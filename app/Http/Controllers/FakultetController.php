<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Validation\Rule;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class FakultetController extends Controller
{
    // Prikaz svih fakulteta
    public function index()
    {
        $fakulteti = Fakultet::with('univerzitet')->get();
        $univerziteti = Univerzitet::all();
        return view('fakultet.index', compact('fakulteti', 'univerziteti'));
    }

    // Dodavanje novog fakulteta
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
            // Upload PDF na Cloudinary
            $uploadedPdf = Cloudinary::uploadApi()->upload(
                $request->file('uputstvo_file')->getRealPath(),
                [
                    'folder' => 'fakulteti/pdf',
                    'resource_type' => 'raw' // PDF se tretira kao raw
                ]
            );
            $validated['uputstvo_file'] = $uploadedPdf['secure_url'];

            // Generiši preview slike prve strane PDF-a
            $previewImage = Cloudinary::uploadApi()->upload(
                $request->file('uputstvo_file')->getRealPath(),
                [
                    'folder' => 'fakulteti/pdf_preview',
                    'resource_type' => 'image',
                    'pages' => 1
                ]
            );
            $validated['uputstvo_preview'] = $previewImage['secure_url'];
        }

        Fakultet::create($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno dodat!');
    }

    // Ažuriranje fakulteta
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
            // Upload PDF
            $uploadedPdf = Cloudinary::uploadApi()->upload(
                $request->file('uputstvo_file')->getRealPath(),
                [
                    'folder' => 'fakulteti/pdf',
                    'resource_type' => 'raw'
                ]
            );
            $validated['uputstvo_file'] = $uploadedPdf['secure_url'];

            // Generiši preview slike prve strane PDF-a
            $previewImage = Cloudinary::uploadApi()->upload(
                $request->file('uputstvo_file')->getRealPath(),
                [
                    'folder' => 'fakulteti/pdf_preview',
                    'resource_type' => 'image',
                    'pages' => 1
                ]
            );
            $validated['uputstvo_preview'] = $previewImage['secure_url'];
        }

        $fakultet->update($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno ažuriran!');
    }

    // Brisanje fakulteta
    public function destroy($id)
    {
        $fakultet = Fakultet::findOrFail($id);
        $fakultet->delete();

        return redirect()->back()->with('success', 'Fakultet uspješno obrisan!');
    }
}