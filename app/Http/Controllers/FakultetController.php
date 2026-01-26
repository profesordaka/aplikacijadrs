<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Fakultet;
use App\Models\Univerzitet;
use Illuminate\Validation\Rule;
use Cloudinary\Cloudinary;

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Dodajemo pravilo za sliku
            'pdf' => 'nullable|mimes:pdf|max:10240',  // Dodajemo pravilo za PDF
        ]);

        // Upload slike na Cloudinary, ako postoji
        if ($request->hasFile('image')) {
            $image = $request->file('image')->storeOnCloudinary([
                'folder' => 'fakulteti/images', // Specifikuj folder
            ]);
            $validated['image_url'] = $image['secure_url'];  // Sačuvaj URL u bazi
        }

        // Upload PDF-a na Cloudinary, ako postoji
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf')->storeOnCloudinary([
                'folder' => 'fakulteti/pdfs',  // Specifikuj folder
                'resource_type' => 'auto',     // Cloudinary automatski prepoznaje tip
            ]);
            $validated['pdf_url'] = $pdf['secure_url'];  // Sačuvaj URL u bazi
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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',  // Dodajemo pravilo za sliku
            'pdf' => 'nullable|mimes:pdf|max:10240',  // Dodajemo pravilo za PDF
        ]);

        // Ako postoji nova slika, uploaduj je na Cloudinary
        if ($request->hasFile('image')) {
            $image = $request->file('image')->storeOnCloudinary([
                'folder' => 'fakulteti/images',
            ]);
            $validated['image_url'] = $image['secure_url'];  // Sačuvaj URL u bazi
        }

        // Ako postoji novi PDF, uploaduj ga na Cloudinary
        if ($request->hasFile('pdf')) {
            $pdf = $request->file('pdf')->storeOnCloudinary([
                'folder' => 'fakulteti/pdfs',
                'resource_type' => 'auto',
            ]);
            $validated['pdf_url'] = $pdf['secure_url'];  // Sačuvaj URL u bazi
        }

        $fakultet->update($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno ažuriran!');
    }

    public function destroy($id)
    {
        $fakultet = Fakultet::findOrFail($id);

        // Prvo ukloniti slike i PDF-ove sa Cloudinary, ako postoji
        if ($fakultet->image_url) {
            $imageId = basename(parse_url($fakultet->image_url, PHP_URL_PATH));
            Cloudinary::destroy($imageId);
        }

        if ($fakultet->pdf_url) {
            $pdfId = basename(parse_url($fakultet->pdf_url, PHP_URL_PATH));
            Cloudinary::destroy($pdfId);
        }

        $fakultet->delete();

        return redirect()->back()->with('success', 'Fakultet uspješno obrisan!');
    }
}

