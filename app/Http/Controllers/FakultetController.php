<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;  // Import Cloudinary facade
use App\Models\Fakultet;

class FakultetController extends Controller
{
    public function update(Request $request, $id)
    {
        // Validacija inputa
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'pdf' => 'nullable|mimes:pdf|max:10240', // Max PDF size is 10MB
        ]);

        // Pronalaženje fakulteta u bazi podataka
        $fakultet = Fakultet::findOrFail($id);

        // Ažuriranje podataka fakulteta
        $fakultet->name = $request->input('name');
        $fakultet->description = $request->input('description');

        // Ako postoji slika, uploaduj je na Cloudinary
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->getRealPath();

            // Upload slike na Cloudinary
            $image = Cloudinary::upload($imagePath, [
                'folder' => 'fakulteti/images',  // Folder u kojem se čuvaju slike
            ]);

            // Spremi URL slike u bazi podataka
            $fakultet->image_url = $image->getSecureUrl();
        }

        // Ako postoji PDF, uploaduj ga na Cloudinary
        if ($request->hasFile('pdf')) {
            $pdfPath = $request->file('pdf')->getRealPath();

            // Upload PDF-a na Cloudinary
            $pdf = Cloudinary::upload($pdfPath, [
                'folder' => 'fakulteti/pdfs',  // Folder u kojem se čuvaju PDF-ovi
                'resource_type' => 'auto',     // Cloudinary automatski prepoznaje tip resursa (npr. PDF)
            ]);

            // Spremi URL PDF-a u bazi podataka
            $fakultet->pdf_url = $pdf->getSecureUrl();
        }

        // Spremi izmene u bazu
        $fakultet->save();

        // Vratiti korisnika na listu fakulteta
        return redirect()->route('fakulteti.index')->with('success', 'Fakultet je uspešno ažuriran!');
    }
}



