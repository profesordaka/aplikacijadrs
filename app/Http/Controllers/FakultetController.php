<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Validation\Rule;
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
            'uputstvo_za_ocjene' => 'nullable|string',
            'univerzitet_id' => 'required|exists:univerziteti,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'uputstvo_file' => 'nullable|mimes:pdf|max:10240',
        ]);

        // Upload slike na Cloudinary
        if ($request->hasFile('image')) {
            $uploadedImage = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'fakulteti/images'
            ]);
            $validated['image_url'] = $uploadedImage->getSecurePath();
        }

        // Upload PDF-a na Cloudinary
        if ($request->hasFile('uputstvo_file')) {
            $uploadedPdf = Cloudinary::upload($request->file('uputstvo_file')->getRealPath(), [
                'folder' => 'fakulteti/pdfs',
                'resource_type' => 'auto'
            ]);
            $validated['uputstvo_file'] = $uploadedPdf->getSecurePath();
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'uputstvo_file' => 'nullable|mimes:pdf|max:10240',
        ]);

        // Ako postoji nova slika
        if ($request->hasFile('image')) {
            $uploadedImage = Cloudinary::upload($request->file('image')->getRealPath(), [
                'folder' => 'fakulteti/images'
            ]);
            $validated['image_url'] = $uploadedImage->getSecurePath();
        }

        // Ako postoji novi PDF
        if ($request->hasFile('uputstvo_file')) {
            $uploadedPdf = Cloudinary::upload($request->file('uputstvo_file')->getRealPath(), [
                'folder' => 'fakulteti/pdfs',
                'resource_type' => 'auto'
            ]);
            $validated['uputstvo_file'] = $uploadedPdf->getSecurePath();
        }

        $fakultet->update($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno ažuriran!');
    }

    public function destroy($id)
    {
        $fakultet = Fakultet::findOrFail($id);

        // Samo brišemo iz baze, fajlovi ostaju na Cloudinary
        $fakultet->delete();

        return redirect()->back()->with('success', 'Fakultet uspješno obrisan!');
    }
}

