<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Predmet;
use App\Models\Fakultet;

class PredmetController extends Controller
{
    public function index(Fakultet $fakultet)
    {
        $predmeti = $fakultet->predmeti()->with('nivoStudija')->get();
        $nivoStudija = \App\Models\NivoStudija::all();
        return view('predmet.index', compact('predmeti', 'fakultet', 'nivoStudija'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sifra_predmeta' => 'required|string|max:255',
            'naziv' => 'required|string|max:255',
            'ects' => 'required|integer|min:1',
            'semestar' => 'required|integer|min:1',
            'fakultet_id' => 'required|exists:fakulteti,id',
            'nivo_studija_id' => 'required|exists:nivo_studija,id',
        ]);

        Predmet::create($validated);

        return redirect()->back()->with('success', 'Predmet uspješno dodat!');
    }

    public function update(Request $request, $id)
    {
        $predmet = Predmet::findOrFail($id);

        $validated = $request->validate([
            'sifra_predmeta' => 'required|string|max:255',
            'naziv' => 'required|string|max:255',
            'ects' => 'required|integer|min:1',
            'semestar' => 'required|integer|min:1',
            'fakultet_id' => 'required|exists:fakulteti,id',
            'nivo_studija_id' => 'required|exists:nivo_studija,id',
        ]);

        $predmet->update($validated);

        return redirect()->route('fakulteti.predmeti.index', $predmet->fakultet_id)->with('success', 'Predmet uspješno ažuriran!');
    }

    public function destroy($id)
    {
        $predmet = Predmet::findOrFail($id);
        $fakultetId = $predmet->fakultet_id; // Save ID before deleting
        $predmet->delete();

        return redirect()->route('fakulteti.predmeti.index', $fakultetId)->with('success', 'Predmet uspješno obrisan!');
    }

    public function import(Request $request, $fakultetId)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
            'level' => 'required|in:basic,master',
        ]);

        $file = $request->file('file');
        $filePath = $file->getPathname();
        $level = $request->input('level');
        
        $fakultet = Fakultet::findOrFail($fakultetId);

        $importer = new \App\Services\SubjectImportService();
        try {
            if (\Illuminate\Support\Str::contains($fakultet->naziv, ['FIT', 'Fakultet za informacione tehnologije'])) {
                 $courses = $importer->loadCoursesFit($filePath, $level);
            } else {
                 $courses = $importer->loadCoursesGeneric($filePath);
            }
        } catch (\Exception $e) {
             return redirect()->back()->withErrors(['file' => 'Error parsing file: ' . $e->getMessage()]);
        }
        
        // Determine study level ID
        $nivoName = ($level === 'basic') ? 'Osnovne studije' : 'Master studije';
        $nivoStudija = \App\Models\NivoStudija::where('naziv', 'LIKE', $nivoName . '%')
                        ->orWhere('naziv', ($level === 'basic' ? 'Osnovne' : 'Master'))
                        ->first();

        $nivoStudijaId = $nivoStudija?->id;

        $count = 0;
        $courseGroups = [];
        foreach ($courses as $c) {
            $key = ($c['Naziv Predmeta'] ?? 'Unknown') . '|' . ($c['Semestar'] ?? 0) . '|' . ((int) ($c['ECTS'] ?? 0));
            if (!isset($courseGroups[$key])) {
                $courseGroups[$key] = [];
            }
            $courseGroups[$key][] = $c;
        }

        foreach ($courseGroups as $key => $groupCourses) {
            $first = $groupCourses[0];
            $name = $first['Naziv Predmeta'] ?? 'Unknown';
            $sem = (int) ($first['Semestar'] ?? 0);
            $ects = (int) ($first['ECTS'] ?? 0);
            $sifra = $first['Sifra Predmeta'] ?? null;
            $engName = $first['Naziv Engleski'] ?? null;

            $existing = Predmet::where('fakultet_id', $fakultet->id)
                                ->where('naziv', $name)
                                ->where('semestar', $sem)
                                ->where('ects', $ects)
                                ->get();
            
            foreach($existing as $exSubject) {
                $updates = [
                    'naziv_engleski' => $engName,
                    'nivo_studija_id' => $exSubject->nivo_studija_id ?? $nivoStudijaId,
                ];
                
                if (empty($exSubject->sifra_predmeta) && !empty($sifra)) {
                    $updates['sifra_predmeta'] = $sifra;
                }

                $exSubject->update($updates);
            }

            $needed = count($groupCourses) - $existing->count();

            if ($needed > 0) {
                for ($i = 0; $i < $needed; $i++) {
                     Predmet::create([
                        'sifra_predmeta' => $sifra ?? 'Unknown',
                        'naziv' => $name,
                        'fakultet_id' => $fakultet->id,
                        'semestar' => $sem,
                        'ects' => $ects,
                        'naziv_engleski' => $engName,
                        'nivo_studija_id' => $nivoStudijaId,
                     ]);
                     $count++;
                }
            }
        }

        return redirect()->back()->with('success', "Uspjesno  importovano $count novih predmeta!");
    }
    
    public function getSubjectsByFaculty(Fakultet $fakultet)
    {
        $predmeti = $fakultet->predmeti()->with('nivoStudija')->get();
        return response()->json($predmeti);
    }
}
