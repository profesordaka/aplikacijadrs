<?php

namespace App\Http\Controllers;

use App\Models\NastavnaLista;
use App\Models\Predmet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NastavnaListaController extends Controller
{
    public function index(Predmet $predmet)
    {
        $nastavneListe = $predmet->nastavneListe()->orderBy('studijska_godina', 'desc')->get();
        return view('nastavna-lista.index', compact('predmet', 'nastavneListe'));
    }

    public function store(Request $request, Predmet $predmet)
    {
        if (auth()->user()->type != 0) {
            abort(403);
        }

        $validated = $request->validate([
            'studijska_godina' => 'required|string|max:20',
            'link' => 'nullable|url',
            'file' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ]);

        // Validate that at least one is provided
        if (!$request->link && !$request->hasFile('file')) {
            return back()->withErrors(['error' => 'Morate unijeti link ili učitati fajl.']);
        }

        $data = [
            'predmet_id' => $predmet->id,
            'fakultet_id' => $predmet->fakultet_id,
            'studijska_godina' => $validated['studijska_godina'],
            'link' => $validated['link'] ?? null,
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $path = $file->store('nastavne_liste', 'public');
            $data['file_path'] = $path;
            $data['file_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getClientMimeType();
        }

        try {
            NastavnaLista::create($data);
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == '23505') { // Postgres Unique violation
                return back()->withErrors(['error' => 'Nastavna lista za ovu studijsku godinu već postoji.']);
            }
            throw $e;
        }

        return back()->with('success', 'Nastavna lista uspješno dodata.');
    }

    public function download(NastavnaLista $nastavnaLista)
    {
        if (!$nastavnaLista->file_path || !Storage::disk('public')->exists($nastavnaLista->file_path)) {
            return back()->withErrors(['error' => 'Fajl ne postoji.']);
        }

        return Storage::disk('public')->download($nastavnaLista->file_path, $nastavnaLista->file_name);
    }

    public function destroy(NastavnaLista $nastavnaLista)
    {
        if (auth()->user()->type != 0) {
            abort(403);
        }

        if ($nastavnaLista->file_path && Storage::disk('public')->exists($nastavnaLista->file_path)) {
            Storage::disk('public')->delete($nastavnaLista->file_path);
        }

        $nastavnaLista->delete();

        return back()->with('success', 'Nastavna lista uspješno obrisana.');
    }
}
