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
            'uputstvo_za_ocjene' => 'nullable|string',
            'univerzitet_id' => 'required|exists:univerziteti,id',
            'uputstvo_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('uputstvo_file')) {
            $validated['uputstvo_file'] = $this->uploadPdfToCloudinary($request->file('uputstvo_file'));
        }

        Fakultet::create($validated);

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
            'uputstvo_za_ocjene' => 'nullable|string',
            'univerzitet_id' => 'required|exists:univerziteti,id',
            'uputstvo_file' => 'nullable|file|mimes:pdf|max:10240',
        ]);

        if ($request->hasFile('uputstvo_file')) {
            $validated['uputstvo_file'] = $this->uploadPdfToCloudinary($request->file('uputstvo_file'));
        }

        $fakultet->update($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno ažuriran!');
    }

    public function destroy($id)
    {
        Fakultet::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Fakultet uspješno obrisan!');
    }

    /**
     * Upload PDF na Cloudinary
     */
    private function uploadPdfToCloudinary($file)
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $originalName = str_replace('.tmp', '', $originalName);
        $publicId = $originalName . '_' . time();

        $pdfUpload = Cloudinary::uploadApi()->upload(
            $file->getRealPath(),
            [
                'folder' => 'fakulteti/pdf',
                'public_id' => $publicId,
                'resource_type' => 'raw',
                'overwrite' => false,
            ]
        );

        return $pdfUpload['secure_url'];
    }

    /**
     * Prikaži PDF u browseru (inline view)
     */
    public function viewPdf(Fakultet $fakultet)
    {
        if (!$fakultet->uputstvo_file) {
            return response('PDF nije dostupan', 404);
        }

        $url = $fakultet->uputstvo_file;
        
        try {
            $pdfContent = file_get_contents($url);
            
            if (!$pdfContent) {
                return response('Greška pri učitavanju PDF-a', 404);
            }

            return response($pdfContent)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="' . $fakultet->naziv . '.pdf"');
            
        } catch (\Exception $e) {
            return response('Greška: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Generiši URL-e za sve stranice PDF-a kao slike koristeći Cloudinary transformation
     */
    public function pdfPages(Fakultet $fakultet)
    {
        if (!$fakultet->uputstvo_file) {
            return response()->json(['error' => 'PDF nije dostupan'], 404);
        }

        try {
            $cloudName = env('CLOUDINARY_CLOUD_NAME');
            $pdfUrl = $fakultet->uputstvo_file;
            $pages = [];
            $encodedPdfUrl = urlencode($pdfUrl);

            for ($page = 1; $page <= 10; $page++) {
                $transformations = "fl_page.{$page},w_800,h_1000,c_fill,f_jpg,q_auto:best";
                $pageUrl = "https://res.cloudinary.com/{$cloudName}/image/fetch/{$transformations}/{$encodedPdfUrl}";
                $pages[] = $pageUrl;
            }

            return response()->json(['pages' => $pages]);

        } catch (\Exception $e) {
            Log::error('PDF Pages Exception', [
                'fakultet_id' => $fakultet->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * PDF Proxy za inline prikaz u browseru
     */
    public function pdfProxy(Fakultet $fakultet)
    {
        if (!$fakultet->uputstvo_file) {
            return response('PDF nije dostupan', 404);
        }

        return redirect($fakultet->uputstvo_file);
    }
}


