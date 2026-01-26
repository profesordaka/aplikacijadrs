<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Cloudinary\Cloudinary;

class FakultetController extends Controller
{
    // Metoda za upload fajla
    public function uploadFile(Request $request)
    {
        // Validacija da fajl bude prisutan i da bude slika (jpg, jpeg, png) ili PDF
        $validatedData = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:20480', // Slike i PDF do 20MB
        ]);

        // Uzimanje fajla iz request-a
        $file = $request->file('file');
        $filePath = $file->getRealPath(); // Putanja do fajla na serveru
        $fileMimeType = $file->getMimeType(); // MIME tip fajla

        try {
            // Provjeravamo tip fajla (ako je PDF)
            if ($fileMimeType == 'application/pdf') {
                // Ako je PDF fajl, koristimo 'resource_type' => 'raw' za upload
                $uploadedFile = \Cloudinary\Cloudinary::upload($filePath, [
                    'resource_type' => 'raw', // Za PDF i druge neslike fajlove
                ]);
            } else {
                // Ako je slika, samo uploadujemo bez dodatnih parametara
                $uploadedFile = \Cloudinary\Cloudinary::upload($filePath);
            }

            // Dobijanje URL-a fajla sa Cloudinary-a
            $uploadedUrl = $uploadedFile->getSecureUrl();

            // Ovde možeš da sačuvaš URL u bazi podataka, npr:
            // $fakultet = Fakultet::find($id);
            // $fakultet->file_url = $uploadedUrl;
            // $fakultet->save();

            // Vraćanje sa uspešnim porukama
            return back()->with('success', 'Fajl je uspešno uploadovan!')->with('fileUrl', $uploadedUrl);
        } catch (\Exception $e) {
            // U slučaju greške
            return back()->with('error', 'Došlo je do greške prilikom upload-a: ' . $e->getMessage());
        }
    }

    // Ostale metode koje se mogu koristiti za update, itd.
    public function update(Request $request, $id)
    {
        // Ostatak tvoje metode za update podataka fakulteta, uključujući upload
    }

}


