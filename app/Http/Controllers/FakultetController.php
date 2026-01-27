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
            $file = $request->file('uputstvo_file');
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
            
            $validated['uputstvo_file'] = $pdfUpload['secure_url'];
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
    $file = $request->file('uputstvo_file');
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
    
    $validated['uputstvo_file'] = $pdfUpload['secure_url'];
}

        $fakultet->update($validated);

        return redirect()->back()->with('success', 'Fakultet uspješno ažuriran!');
    }

    public function destroy($id)
    {
        Fakultet::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Fakultet uspješno obrisan!');
    }


public function download(Fakultet $fakultet)
{
    if (!$fakultet->uputstvo_file) {
        return redirect()->back()->with('error', 'PDF nije dostupan');
    }

    $url = $fakultet->uputstvo_file;
    $fileName = $fakultet->naziv . '.pdf';

    try {
        $pdfContent = file_get_contents($url);
        
        if (!$pdfContent) {
            return redirect()->back()->with('error', 'Greška pri preuzimanju PDF-a');
        }

        return response($pdfContent, 200)
            ->header('Content-Type', 'application/octet-stream')
            ->header('Content-Disposition', 'attachment; filename="' . addslashes($fileName) . '"')
            ->header('Content-Length', strlen($pdfContent))
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
        
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Greška: ' . $e->getMessage());
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
        
        // URL encode umesto base64 - Cloudinary preferira URL encoding za fetch
        $encodedPdfUrl = urlencode($pdfUrl);
        
        // Generiši URL-e za prvih 10 stranica (možeš povećati ako treba)
        for ($page = 1; $page <= 10; $page++) {
            // Koristi fl_page za stranicu, w i h za dimenzije, f_jpg za format
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
 * PDF Proxy za PDF.js - CORS-safe PDF učitavanje
 * Koristi se u JavaScript-u za učitavanje PDF-a na canvas
 */
public function pdfProxy(Fakultet $fakultet)
{
    if (!$fakultet->uputstvo_file) {
        return response('PDF nije dostupan', 404);
    }

    try {
        // Koristi jednostavan HTTP zahtev za preuzimanje PDF-a sa Cloudinary-ja
        // Cloudinary secure_url-ovi bi trebalo da budu javno dostupni
        $response = Http::timeout(30)
            ->withoutVerifying()
            ->withHeaders([
                'Accept' => 'application/pdf',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
            ])
            ->get($fakultet->uputstvo_file);
        
        if ($response->successful()) {
            $body = $response->body();
            
            // Proveri da li je stvarno PDF (počinje sa %PDF)
            if (substr($body, 0, 4) === '%PDF' || str_contains($response->header('Content-Type') ?? '', 'application/pdf')) {
                return response($body, 200, [
                    'Content-Type' => 'application/pdf',
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                    'Access-Control-Allow-Headers' => 'Content-Type',
                    'Cache-Control' => 'public, max-age=86400',
                    'Content-Disposition' => 'inline; filename="' . $fakultet->naziv . '.pdf"',
                    'Content-Length' => strlen($body),
                ]);
            } else {
                Log::warning('PDF Proxy: Response is not a valid PDF', [
                    'fakultet_id' => $fakultet->id,
                    'pdf_url' => $fakultet->uputstvo_file,
                    'content_type' => $response->header('Content-Type'),
                    'first_bytes' => substr($body, 0, 100)
                ]);
            }
        }
        
        // Ako HTTP zahtev ne radi, probaj redirect na direktan URL
        // Ovo će omogućiti browser-u da direktno učita PDF
        Log::warning('PDF Proxy: HTTP request failed, redirecting to direct URL', [
            'fakultet_id' => $fakultet->id,
            'pdf_url' => $fakultet->uputstvo_file,
            'status' => $response->status() ?? 'unknown'
        ]);
        
        return redirect($fakultet->uputstvo_file);
        
    } catch (\Exception $e) {
        Log::error('PDF Proxy Exception', [
            'fakultet_id' => $fakultet->id,
            'pdf_url' => $fakultet->uputstvo_file,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        // Fallback na direktan URL redirect
        return redirect($fakultet->uputstvo_file);
    }
}

/**
 * PDF Preview - Generiša Cloudinary transformation URL
 * Pretvara PDF prvi page u JPG sliku
 */
public function pdfPreview(Fakultet $fakultet)
{
    if (!$fakultet->uputstvo_file) {
        return response('PDF nije dostupan', 404);
    }

    try {
        $pdfUrl = $fakultet->uputstvo_file;
        $cloudName = env('CLOUDINARY_CLOUD_NAME');
        
        // Generiši Cloudinary transformation URL
        // fl_page.1 = first page, w_400 = width, h_500 = height, f_jpg = format, q_auto:best = auto quality
        $transformations = 'fl_page.1,w_400,h_500,c_fill,f_jpg,q_auto:best';
        $encodedUrl = base64_encode($pdfUrl);
        
        // Cloudinary fetch URL - direktna transformacija PDF-a u JPG
        $previewUrl = "https://res.cloudinary.com/{$cloudName}/image/fetch/{$transformations}/{$encodedUrl}";
        
        // Redirect na preview
        return redirect($previewUrl);
        
    } catch (\Exception $e) {
        return response('Greška: ' . $e->getMessage(), 500);
    }
}


}


