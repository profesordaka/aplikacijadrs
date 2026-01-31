<?php

namespace App\Http\Controllers;

use App\Models\Fakultet;
use App\Models\LearningAgreement;
use App\Models\Mobilnost;
use App\Models\Student;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Element\Text;
use PhpOffice\PhpWord\Element\TextRun;
use PhpOffice\PhpWord\Element\Table;
use PhpOffice\PhpWord\SimpleType\JcTable;
use PhpOffice\PhpWord\SimpleType\Jc;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\MobilnostDokument;
use ZipArchive;


class MobilityController extends Controller
{
    public function index()
    {
        $students = Student::where('status', 'mobilnost')->whereHas('fakulteti', function($query) {
            $query->where('naziv', 'FIT');
        })->orderBy('ime')->orderBy('prezime')->get();
        $fakulteti = Fakultet::with('predmeti')->orderBy('naziv')->get();
        return view('mobility.index', compact('students', 'fakulteti'));
    }


    public function save(Request $request)
    {
        try {
            $mobilnost = $this->storeMobility($request);
            return redirect()->route('admin.mobility.show', $mobilnost->id)->with('success', 'Mobilnost uspješno sačuvana.');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Save failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Failed to save mobility: ' . $e->getMessage())->withInput();
        }
    }

    public function export(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:studenti,id',
            'fakultet_id' => 'required|exists:fakulteti,id',
            'courses' => 'nullable|string',
        ]);

        $student = \App\Models\Student::findOrFail($request->student_id);
        $fakultet = \App\Models\Fakultet::findOrFail($request->fakultet_id);
        $coursesPayload = json_decode($request->input('courses'), true) ?? [];

        // Map foreign IDs to Names for the snippet logic
        // format: [ "FitName" => ["ForeignName1", "ForeignName2"] ]
        $links = [];
        $foreignIds = [];
        foreach ($coursesPayload as $fitName => $fIds) {
            foreach ($fIds as $fid) {
                $foreignIds[] = $fid;
            }
        }

        $foreignCheck = \App\Models\Predmet::whereIn('id', $foreignIds)->pluck('naziv', 'id');
        $foreignEctsMap = \App\Models\Predmet::whereIn('id', $foreignIds)->pluck('ects', 'naziv'); // Map Name -> ECTS for snippet logic compatibility

        foreach ($coursesPayload as $fitName => $fIds) {
            $names = [];
            foreach ($fIds as $fid) {
                if (isset($foreignCheck[$fid])) {
                    $names[] = $foreignCheck[$fid];
                }
            }
            if (!empty($names)) {
                $links[$fitName] = $names;
            }
        }

        // --- Snippet Logic Adapted ---
        $ime = $student->ime;
        $prezime = $student->prezime;
        $brojIndeksa = $student->indeks; // Assuming column is 'indeks' or 'broj_indeksa' -> Model says 'indeks' typically, let's check
        // Looking at student search JS, it uses 'indeks'. 
        // Controller 'save' validation used 'exists:studenti,id'.
        // I will trust $student->indeks or fallback. 
        // Student model is not visible but JS showed `s.indeks`.
        $brojIndeksa = $student->indeks ?? $student->broj_indeksa ?? '';
        $fakultetNaziv = $fakultet->naziv;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText('Mobilnost', ['bold' => true, 'size' => 16]);
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['size' => 12]);
        $textRun->addText('Student osnovnih studija ');
        $textRun->addText("{$ime} {$prezime} {$brojIndeksa}", ['bold' => true]);
        $textRun->addText(' će boraviti na ');
        $textRun->addText($fakultetNaziv, ['bold' => true]);
        $textRun->addText('.');

        $section->addText(
            'Studentu ce se priznavati sledeci ispiti:',
            ['size' => 12]
        );
        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER
        ];
        $phpWord->addTableStyle('CoursesTable', $tableStyle);
        $table = $section->addTable('CoursesTable');

        // Headeri
        $headers = ['R.br', 'Predmet (FIT)', 'Semestar', 'ECTS', 'Priznaje se', 'ECTS'];
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(2000)->addText($header, ['bold' => true]);
        }

        $rowNum = 1;
        // Fetch details for all FIT subjects involved
        $allFitSubjects = array_keys($links);
        $fitSubjectsDetails = \App\Models\Predmet::whereIn('naziv', $allFitSubjects)
            ->whereHas('fakultet', function ($q) {
                $q->where('naziv', 'FIT');
            })
            ->get()
            ->keyBy('naziv');

        foreach ($links as $fitSubject => $linkedSubjects) {
            if (empty($linkedSubjects))
                continue;

            $fitSubjectModel = $fitSubjectsDetails[$fitSubject] ?? null;
            $term = $fitSubjectModel ? $fitSubjectModel->semestar : '';
            $ects = $fitSubjectModel ? $fitSubjectModel->ects : '';

            $table->addRow();
            $table->addCell(800)->addText($rowNum++);
            $table->addCell(3000)->addText($fitSubject);
            $table->addCell(1200)->addText($term);
            $table->addCell(800)->addText($ects);

            $textRun = $table->addCell(4000)->addTextRun();
            foreach ($linkedSubjects as $i => $subj) {
                $textRun->addText($subj);
                if ($i < count($linkedSubjects) - 1) {
                    $textRun->addTextBreak();
                }
            }

            $totalEcts = 0;
            foreach ($linkedSubjects as $subj) {
                $subj = trim($subj);
                // Use our pre-fetched map or the one from snippet logic if preferred. 
                // Snippet logic fetched by name. I have a map by name prepared above ($foreignEctsMap).
                $totalEcts += $foreignEctsMap[$subj] ?? 0;
            }

            $table->addCell(800)->addText($totalEcts);
        }

        $section->addTextBreak(2);
        $section->addText('Dekan,', ['bold' => true]);
        $section->addText('___________________________');

        // Sanitize filename
        $safeIndeks = str_replace(['/', '\\'], '_', $brojIndeksa);
        $fileName = 'Mobilnost_' . $safeIndeks . '_' . date('Ymd_His') . '.docx';
        $filePath = storage_path("app/public/$fileName");

        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    private function storeMobility(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:studenti,id',
            'fakultet_id' => 'required|exists:fakulteti,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'tip_mobilnosti' => 'required|in:Semestralna mobilnost,Godišnja mobilnost',
            'courses' => 'nullable|string', // JSON string
        ]);

        $studentId = $request->student_id;
        $fakultetId = $request->fakultet_id;
        $datumPocetka = $request->start_date;
        $datumKraja = $request->end_date;
        $tipMobilnosti = $request->tip_mobilnosti;

        // Frontend sends 'courses' as JSON string in hidden input
        $coursesPayload = json_decode($request->input('courses'), true) ?? [];

        // Create or update Mobilnost
        // We might want to avoid duplicates if same student/faculty/dates exist?
        // For now, create new as per original logic implies
        $mobilnost = Mobilnost::create([
            'student_id' => $studentId,
            'fakultet_id' => $fakultetId,
            'datum_pocetka' => $datumPocetka,
            'datum_kraja' => $datumKraja,
            'tip_mobilnosti' => $tipMobilnosti,
        ]);

        // Process courses mapping
        // Format of $coursesPayload: { "FIT Subject Name": [foreign_id1, foreign_id2], ... }

        foreach ($coursesPayload as $fitSubjectName => $foreignIds) {
            $fitSubjectName = trim($fitSubjectName);

            // Find FIT Subject
            $fitPredmet = \App\Models\Predmet::where('naziv', $fitSubjectName)
                ->whereHas('fakultet', function ($q) {
                    $q->where('naziv', 'FIT');
                })->first();

            if (!$fitPredmet) {
                // Fallback search? Original code had complex fallback. 
                // Assuming reliable search from UI now, but keeping safe.
                continue;
            }

            foreach ($foreignIds as $foreignId) {
                $foreignPredmet = \App\Models\Predmet::find($foreignId);
                if (!$foreignPredmet)
                    continue;

                LearningAgreement::create([
                    'mobilnost_id' => $mobilnost->id,
                    'fit_predmet_id' => $fitPredmet->id,
                    'strani_predmet_id' => $foreignPredmet->id,
                    'ocjena' => null // Initial grade
                ]);
            }
        }

        return $mobilnost;
    }

    public function upload(Request $request)
    {
        $request->validate([
            'word_file' => 'required|mimes:doc,docx|max:10240',
        ]);

        $file = $request->file('word_file');
        $path = $file->storeAs('uploads', $file->getClientOriginalName());

        $courses = [];
        $this->findMissingFitSubjects(storage_path('app/private/' . $path), $courses);

        return redirect()->route($this->getRedirectRoute())->with('courses', $courses)->withInput();
    }

    private function getRedirectRoute(): string
    {
        $user = Auth::user();
        if ((int) $user->type === 0) {
            return 'admin.mobility';
        }
        return 'profesor.mobility';
    }

    private function getElementText($element): string
    {
        $text = '';

        if ($element instanceof Text) {
            $text .= $element->getText();
        } elseif ($element instanceof TextRun) {
            foreach ($element->getElements() as $child) {
                $text .= $this->getElementText($child) . ' ';
            }
        }
        return trim($text);
    }

    private function getColumnValue(array $rowData, array $headerMap, array $possibleNames): ?string
    {
        foreach ($possibleNames as $name) {
            if (isset($headerMap[$name])) {
                $idxs = (array) $headerMap[$name];
                foreach ($idxs as $i) {
                    if (!empty($rowData[$i]))
                        return $rowData[$i];
                }
            }
        }
        return null;
    }

    private function findMissingFitSubjects(string $filePath, array &$missingSubjects)
    {
        $filePath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $filePath);

        if (!file_exists($filePath)) {
            throw new \Exception("File not found at path: $filePath");
        }

        try {
            $phpWord = IOFactory::load($filePath);
        } catch (\Exception $e) {
            Log::error('PhpWord failed to load file: ' . $filePath . ' | ' . $e->getMessage());
            throw $e;
        }

        $foundSubjectNames = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if (!($element instanceof Table))
                    continue;

                $rows = $element->getRows();
                $headerMap = [];
                $headerRowFound = false;
                $headerRowValues = [];

                foreach ($rows as $row) {
                    $rowData = [];
                    foreach ($row->getCells() as $cell) {
                        $cellText = '';
                        foreach ($cell->getElements() as $cellElement) {
                            $cellText .= $this->getElementText($cellElement) . ' ';
                        }
                        $rowData[] = trim(preg_replace('/\s+/', ' ', $cellText));
                    }

                    $rowData = array_map(fn($v) => trim($v), $rowData);

                    if (!$headerRowFound) {
                        $normalized = array_map(fn($v) => strtolower(trim($v)), $rowData);
                        if (count(array_intersect($normalized, ['term', 'semester', 'course', 'subject', 'title', 'grade', 'ects', 'credits', 'points'])) >= 2) {
                            foreach ($normalized as $i => $header) {
                                $headerMap[$header][] = $i;
                            }
                            $headerRowFound = true;
                            $headerRowValues = $rowData;
                        }
                        continue;
                    }

                    if ($rowData === $headerRowValues) {
                        continue;
                    }

                    $gradeCandidates = $headerMap['grade'] ?? [];
                    $gradeFound = false;
                    foreach ($gradeCandidates as $colIdx) {
                        $value = $rowData[$colIdx] ?? null;
                        if ($value && preg_match('/^[A-F][+-]?$/i', trim($value))) {
                            $gradeFound = true;
                            break;
                        }
                    }

                    // If NOT grade found, we skip (we only want passed subjects from ToR)
                    if (!$gradeFound) {
                        continue;
                    }

                    $course = $this->getColumnValue($rowData, $headerMap, ['course', 'subject', 'title']);
                    if ($course) {
                        $foundSubjectNames[] = $course;
                    }
                }
            }
        }

        // Fetch all FIT subjects
        $fitSubjects = \App\Models\Predmet::whereHas('fakultet', function ($q) {
            $q->where('naziv', 'FIT');
        })->get();

        $foundNormalized = array_map(fn($n) => strtolower(str_replace(' ', '', trim($n))), $foundSubjectNames);

        foreach ($fitSubjects as $fitData) {
            $fitNameNorm = strtolower(str_replace(' ', '', trim($fitData->naziv)));
            if (!in_array($fitNameNorm, $foundNormalized)) {
                $missingSubjects[] = $fitData->naziv;
            }
        }
    }
    public function show($id)
    {
        $mobilnost = Mobilnost::with(['student', 'fakultet', 'learningAgreements.fitPredmet', 'learningAgreements.straniPredmet'])->findOrFail($id);
        return view('mobility.show', compact('mobilnost'));
    }

    public function updateGrade(Request $request, $id)
    {
        $request->validate([
            'ocjena' => 'nullable|string|max:10',
        ]);

        $la = LearningAgreement::findOrFail($id);
        
        if ($la->mobilnost->is_locked) {
            return response()->json(['message' => 'Mobility is locked. Cannot update grades.'], 403);
        }

        $la->update(['ocjena' => $request->ocjena]);

        return response()->json(['message' => 'Ocjena uspješno ažurirana.']);
    }

    public function updateGrades(Request $request, $id)
    {
        $request->validate([
            'grades' => 'required|array',
            'grades.*' => 'nullable|string|max:10',
        ]);

        $mobilnost = Mobilnost::findOrFail($id);

        if ($mobilnost->is_locked) {
            return response()->json(['message' => 'Mobility is locked. Cannot update grades.'], 403);
        }

        foreach ($request->grades as $laId => $grade) {
            $la = LearningAgreement::where('mobilnost_id', $mobilnost->id)->where('id', $laId)->first();
            if ($la) {
                $la->update(['ocjena' => $grade]);
            }
        }

        return response()->json(['message' => 'Grades updated successfully.']);
    }
    public function exportWord($id)
    {
        $mobilnost = Mobilnost::with(['student', 'fakultet', 'learningAgreements.fitPredmet', 'learningAgreements.straniPredmet'])
            ->findOrFail($id);

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText('Predlog za priznavanje ispita', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['size' => 12]);
        $textRun->addText('Student osnovnih studija ');
        $textRun->addText("{$mobilnost->student->ime} {$mobilnost->student->prezime}", ['bold' => true]);
        $textRun->addText(", broj indeksa ");
        $textRun->addText($mobilnost->student->br_indexa, ['bold' => true]);
        $textRun->addText(", boravio je na ");
        $textRun->addText($mobilnost->fakultet->naziv, ['bold' => true]);
        $textRun->addText(". Na osnovu sporazuma o mobilnosti i transkripta ocjena, studentu treba da se priznaju sledeći ispiti:");
        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER,
            'bgColor' => 'FFFFCC'
        ];
        $phpWord->addTableStyle('GradesTable', $tableStyle);
        $table = $section->addTable('GradesTable');

        $headers = ['R.br', 'Predmet (FIT)', 'Semestar', 'ECTS (FIT)', 'Priznaje se', 'Ocjena', 'ECTS'];
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(2000, ['bgColor' => 'FFFFFF'])->addText($header, ['name' => 'Times New Roman', 'bold' => true, 'italic' => true]);
        }

        $groupedAgreements = $mobilnost->learningAgreements->groupBy('fit_predmet_id');

        $rowNum = 1;
        foreach ($groupedAgreements as $fitPredmetId => $agreements) {
            $fitPredmet = $agreements->first()->fitPredmet;

            if (!$fitPredmet)
                continue;

            $fitSubjectName = $fitPredmet->naziv;
            $semester = $fitPredmet->semestar;
            $fitEcts = $fitPredmet->ects;

            $foreignSubjects = [];
            $totalForeignEcts = 0;

            $gradeSum = 0;
            $gradeCount = 0;
            $gradeMap = ['A' => 10, 'B' => 9, 'C' => 8, 'D' => 7, 'E' => 6];

            foreach ($agreements as $la) {
                if ($la->straniPredmet) {
                    $foreignSubjects[] = $la->straniPredmet->naziv;
                    $totalForeignEcts += $la->straniPredmet->ects;
                }

                if (!empty($la->ocjena)) {
                    $rawGrade = strtoupper(trim($la->ocjena));
                    if (isset($gradeMap[$rawGrade])) {
                        $gradeSum += $gradeMap[$rawGrade];
                        $gradeCount++;
                    } elseif (is_numeric($rawGrade)) {
                        $gradeSum += (float) $rawGrade;
                        $gradeCount++;
                    }
                }
            }

            if ($gradeCount > 0) {
                $numericGrade = (int) round($gradeSum / $gradeCount);
                $reverseMap = [10 => 'A', 9 => 'B', 8 => 'C', 7 => 'D', 6 => 'E'];
                $grade = $reverseMap[$numericGrade] ?? $numericGrade;
            } else {
                $grade = '';
            }

            $foreignSubjectsString = implode(', ', $foreignSubjects);

            $table->addRow();
            $table->addCell(800)->addText($rowNum++);
            $table->addCell(3000)->addText($fitSubjectName);
            $table->addCell(1000)->addText($semester);
            $table->addCell(1000)->addText($fitEcts);
            $table->addCell(3000)->addText($foreignSubjectsString);
            $table->addCell(1000)->addText($grade);
            $table->addCell(1000)->addText($totalForeignEcts);
        }

        $safeIndeks = str_replace(['/', '\\'], '_', $mobilnost->student->br_indexa);
        $fileName = 'Mobility_Grades_' . $safeIndeks . '_' . date('Ymd_His') . '.docx';
        $filePath = storage_path("app/public/$fileName");

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function destroy($id)
    {
        $mobilnost = Mobilnost::findOrFail($id);
        $mobilnost->delete();

        return redirect()->back()->with('success', 'Mobility record deleted successfully.');
    }

    public function getStudentSubjects(Request $request)
    {
        $studentId = $request->input('student_id');
        $student = \App\Models\Student::findOrFail($studentId);

        $currentYear = $student->godina_studija;

        // Fetch Unpassed Subjects from Previous Years
        $unpassedSubjects = collect([]);
        $previousSemesters = [];

        if ($currentYear == 2) {
            $previousSemesters = [1, 2];
        } elseif ($currentYear == 3) {
            $previousSemesters = [1, 2, 3, 4];
        }

        if (!empty($previousSemesters)) {
            $passedSubjectIds = $student->predmeti()
                ->wherePivot('grade', '>=', 6)
                ->pluck('predmeti.id')
                ->toArray();

            $unpassedSubjects = \App\Models\Predmet::whereIn('semestar', $previousSemesters)
                ->whereHas('nivoStudija', function ($q) {
                    $q->where('naziv', 'Osnovne');
                })
                ->whereHas('fakultet', function ($q) {
                    $q->where('naziv', 'FIT');
                })
                ->whereNotIn('id', $passedSubjectIds)
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'naziv' => $p->naziv]);
        }

        // Fetch Next Year Subjects
        $nextYearSubjects = collect([]);
        if ($currentYear == 1) {
            // Next is Year 2 (Semesters 3, 4)
            $nextYearSubjects = \App\Models\Predmet::whereIn('semestar', [3, 4])
                ->whereHas('nivoStudija', function ($q) {
                    $q->where('naziv', 'Osnovne');
                })
                ->whereHas('fakultet', function ($q) {
                    $q->where('naziv', 'FIT');
                })
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'naziv' => $p->naziv]);
        } elseif ($currentYear == 2) {
            // Next is Year 3 (Semesters 5, 6)
            $nextYearSubjects = \App\Models\Predmet::whereIn('semestar', [5, 6])
                ->whereHas('nivoStudija', function ($q) {
                    $q->where('naziv', 'Osnovne');
                })
                ->whereHas('fakultet', function ($q) {
                    $q->where('naziv', 'FIT');
                })
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'naziv' => $p->naziv]);
        } elseif ($currentYear == 3) {
            // Next is Master (Semesters 1, 2 of Master)
            // Assuming Master semesters are stored as 1, 2 in DB linked to Master level
            $nextYearSubjects = \App\Models\Predmet::whereIn('semestar', [1, 2])
                ->whereHas('nivoStudija', function ($q) {
                    $q->where('naziv', 'Master');
                })
                ->whereHas('fakultet', function ($q) {
                    $q->where('naziv', 'FIT');
                })
                ->get()
                ->map(fn($p) => ['id' => $p->id, 'naziv' => $p->naziv]);
        }

        return response()->json([
            'unpassed' => $unpassedSubjects->map(fn($p) => ['id' => $p['id'], 'naziv' => $p['naziv'], 'ects' => \App\Models\Predmet::find($p['id'])->ects]),
            'next_year' => $nextYearSubjects->map(fn($p) => ['id' => $p['id'], 'naziv' => $p['naziv'], 'ects' => \App\Models\Predmet::find($p['id'])->ects])
        ]);
    }

    public function getFacultySubjects(Request $request)
    {
        $facultyId = $request->input('fakultet_id');
        if (!$facultyId)
            return response()->json([], 400);

        $subjects = \App\Models\Predmet::where('fakultet_id', $facultyId)
            ->orderBy('naziv')
            ->get(['id', 'naziv', 'ects']);

        return response()->json($subjects);
    }

    public function lock($id)
    {
        $mobilnost = Mobilnost::findOrFail($id);
        $mobilnost->update(['is_locked' => true]);
        
        return redirect()->back()->with('success', 'Mobilnost uspješno zaključena.');
    }

    public function documents(Request $request, $id)
    {
        $mobilnost = Mobilnost::findOrFail($id);
        
        // Ensure default documents exist
        $this->ensureDefaultDocuments($mobilnost);
        
        $documents = $mobilnost->documents()->get();
        
        return response()->json($documents);
    }

    public function uploadDocument(Request $request, $id)
    {
        $mobilnost = Mobilnost::findOrFail($id);

        if ($mobilnost->is_locked) {
            return response()->json(['message' => 'Mobility is locked.'], 403);
        }

        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
        ]);

        $file = $request->file('file');
        
        $filename = $file->getClientOriginalName();
        $path = $file->storeAs("mobility_docs/{$mobilnost->id}", $filename);

        $doc = MobilnostDokument::create([
            'mobilnost_id' => $mobilnost->id,
            'name' => $filename,
            'path' => $path,
            'type' => 'other'
        ]);

        return response()->json($doc);
    }

    public function deleteDocument($id, $docId)
    {
        $mobilnost = Mobilnost::findOrFail($id);

        if ($mobilnost->is_locked) {
            return response()->json(['message' => 'Mobility is locked.'], 403);
        }

        $doc = MobilnostDokument::where('mobilnost_id', $mobilnost->id)->findOrFail($docId);

        if ($doc->type !== 'other') {
            return response()->json(['message' => 'Cannot delete default documents.'], 403);
        }

        Storage::delete($doc->path);
        $doc->delete();

        return response()->json(['message' => 'Document deleted successfully.']);
    }

    public function exportZip($id)
    {
        $mobilnost = Mobilnost::findOrFail($id);
        $this->ensureDefaultDocuments($mobilnost);
        $documents = $mobilnost->documents()->get();

        $zip = new ZipArchive;
        $zipFileName = 'Mobilnost_Dokumenti_' . $mobilnost->student->br_indexa . '.zip';
        $zipPath = storage_path('app/public/' . $zipFileName);

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach ($documents as $doc) {
                $fullPath = Storage::path($doc->path);
                
                if (file_exists($fullPath)) {
                    $zip->addFile($fullPath, $doc->name);
                }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }

    private function ensureDefaultDocuments(Mobilnost $mobilnost)
    {
        $laPath = $this->generateLearningAgreement($mobilnost);
        MobilnostDokument::updateOrCreate(
            [
                'mobilnost_id' => $mobilnost->id,
                'type' => 'learning_agreement'
            ],
            [
                'name' => 'Learning Agreement.docx',
                'path' => $laPath
            ]
        );

        $trPath = $this->generateTranscript($mobilnost);
        MobilnostDokument::updateOrCreate(
            [
                'mobilnost_id' => $mobilnost->id,
                'type' => 'transcript'
            ],
            [
                'name' => 'Ocjene nakon mobilnosti.docx',
                'path' => $trPath
            ]
        );
    }

    private function generateLearningAgreement(Mobilnost $mobilnost)
    {
        $student = $mobilnost->student;
        $fakultet = $mobilnost->fakultet;
        
        $ime = $student->ime;
        $prezime = $student->prezime;
        $brojIndeksa = $student->br_indexa; 
        $fakultetNaziv = $fakultet->naziv;

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText('Mobilnost', ['bold' => true, 'size' => 16]);
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['size' => 12]);
        $textRun->addText('Student osnovnih studija ');
        $textRun->addText("{$ime} {$prezime} {$brojIndeksa}", ['bold' => true]);
        $textRun->addText(' će boraviti na ');
        $textRun->addText($fakultetNaziv, ['bold' => true]);
        $textRun->addText('.');

        $section->addText(
            'Studentu ce se priznavati sledeci ispiti:',
            ['size' => 12]
        );
        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER
        ];
        $phpWord->addTableStyle('CoursesTable', $tableStyle);
        $table = $section->addTable('CoursesTable');

        // Headeri
        $headers = ['R.br', 'Predmet (FIT)', 'Semestar', 'ECTS', 'Priznaje se', 'ECTS'];
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(2000)->addText($header, ['bold' => true]);
        }

        $groupedAgreements = $mobilnost->learningAgreements->groupBy('fit_predmet_id');
        $rowNum = 1;

        foreach ($groupedAgreements as $fitPredmetId => $agreements) {
            $fitPredmet = $agreements->first()->fitPredmet;
            if (!$fitPredmet) continue;

            $fitName = $fitPredmet->naziv;
            $term = $fitPredmet->semestar;
            $ects = $fitPredmet->ects;

            $foreignSubjects = [];
            $totalForeignEcts = 0;
            foreach ($agreements as $la) {
                if ($la->straniPredmet) {
                    $foreignSubjects[] = $la->straniPredmet->naziv;
                    $totalForeignEcts += $la->straniPredmet->ects;
                }
            }

            $table->addRow();
            $table->addCell(800)->addText($rowNum++);
            $table->addCell(3000)->addText($fitName);
            $table->addCell(1200)->addText($term);
            $table->addCell(800)->addText($ects);

            $textRun = $table->addCell(4000)->addTextRun();
            foreach ($foreignSubjects as $i => $subj) {
                $textRun->addText($subj);
                if ($i < count($foreignSubjects) - 1) {
                    $textRun->addTextBreak();
                }
            }

            $table->addCell(800)->addText($totalForeignEcts);
        }

        $section->addTextBreak(2);
        $section->addText('Dekan,', ['bold' => true]);
        $section->addText('___________________________');

        $fileName = 'Learning_Agreement_' . $mobilnost->id . '.docx';
        $path = "mobility_docs/{$mobilnost->id}/" . $fileName;
        
        // Save to storage
        $fullPath = Storage::path($path);
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }
        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        return $path;
    }

    private function generateTranscript(Mobilnost $mobilnost)
    {
        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addText('Predlog za priznavanje ispita', ['bold' => true, 'size' => 16], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $section->addTextBreak(1);

        $textRun = $section->addTextRun(['size' => 12]);
        $textRun->addText('Student osnovnih studija ');
        $textRun->addText("{$mobilnost->student->ime} {$mobilnost->student->prezime}", ['bold' => true]);
        $textRun->addText(", broj indeksa ");
        $textRun->addText($mobilnost->student->br_indexa, ['bold' => true]);
        $textRun->addText(", boravio je na ");
        $textRun->addText($mobilnost->fakultet->naziv, ['bold' => true]);
        $textRun->addText(". Na osnovu sporazuma o mobilnosti i transkripta ocjena, studentu treba da se priznaju sledeći ispiti:");
        $section->addTextBreak(1);

        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 80,
            'alignment' => JcTable::CENTER,
            'bgColor' => 'FFFFCC'
        ];
        $phpWord->addTableStyle('GradesTable', $tableStyle);
        $table = $section->addTable('GradesTable');

        $headers = ['R.br', 'Predmet (FIT)', 'Semestar', 'ECTS (FIT)', 'Priznaje se', 'Ocjena', 'ECTS'];
        $table->addRow();
        foreach ($headers as $header) {
            $table->addCell(2000, ['bgColor' => 'FFFFFF'])->addText($header, ['name' => 'Times New Roman', 'bold' => true, 'italic' => true]);
        }

        $groupedAgreements = $mobilnost->learningAgreements->groupBy('fit_predmet_id');

        $rowNum = 1;
        foreach ($groupedAgreements as $fitPredmetId => $agreements) {
            $fitPredmet = $agreements->first()->fitPredmet;

            if (!$fitPredmet)
                continue;

            $fitSubjectName = $fitPredmet->naziv;
            $semester = $fitPredmet->semestar;
            $fitEcts = $fitPredmet->ects;

            $foreignSubjects = [];
            $totalForeignEcts = 0;

            $gradeSum = 0;
            $gradeCount = 0;
            $gradeMap = ['A' => 10, 'B' => 9, 'C' => 8, 'D' => 7, 'E' => 6];

            foreach ($agreements as $la) {
                if ($la->straniPredmet) {
                    $foreignSubjects[] = $la->straniPredmet->naziv;
                    $totalForeignEcts += $la->straniPredmet->ects;
                }

                if (!empty($la->ocjena)) {
                    $rawGrade = strtoupper(trim($la->ocjena));
                    if (isset($gradeMap[$rawGrade])) {
                        $gradeSum += $gradeMap[$rawGrade];
                        $gradeCount++;
                    } elseif (is_numeric($rawGrade)) {
                        $gradeSum += (float) $rawGrade;
                        $gradeCount++;
                    }
                }
            }

            if ($gradeCount > 0) {
                $numericGrade = (int) round($gradeSum / $gradeCount);
                $reverseMap = [10 => 'A', 9 => 'B', 8 => 'C', 7 => 'D', 6 => 'E'];
                $grade = $reverseMap[$numericGrade] ?? $numericGrade;
            } else {
                $grade = '';
            }

            $foreignSubjectsString = implode(', ', $foreignSubjects);

            $table->addRow();
            $table->addCell(800)->addText($rowNum++);
            $table->addCell(3000)->addText($fitSubjectName);
            $table->addCell(1000)->addText($semester);
            $table->addCell(1000)->addText($fitEcts);
            $table->addCell(3000)->addText($foreignSubjectsString);
            $table->addCell(1000)->addText($grade);
            $table->addCell(1000)->addText($totalForeignEcts);
        }

        $fileName = 'Transcript_' . $mobilnost->id . '.docx';
        $path = "mobility_docs/{$mobilnost->id}/" . $fileName;
        
        $fullPath = Storage::path($path);
        if (!file_exists(dirname($fullPath))) {
            mkdir(dirname($fullPath), 0755, true);
        }

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($fullPath);

        return $path;
    }
}
