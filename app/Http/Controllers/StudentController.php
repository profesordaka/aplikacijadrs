<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\NivoStudija;
use App\Models\Fakultet;
use App\Models\Predmet;
use Illuminate\Http\Request;
use App\Services\TorImportService;
use Illuminate\Support\Facades\Log;

class StudentController extends Controller
{
  public function index(Request $request)
  {
    $query = Student::with(['nivoStudija', 'fakulteti']);

    if ($request->has('status') && in_array($request->status, ['mobilnost', 'prepis'])) {
        $query->where('status', $request->status);
    }
    
    $students = $query->orderBy('created_at', 'desc')->get();
    $nivoStudija = NivoStudija::all();
    $fakulteti = Fakultet::all();

    return view('students.index', compact('students', 'nivoStudija', 'fakulteti'));
  }

  public function create()
  {
    $nivoStudija = NivoStudija::all();
    $predmeti = collect(); // Start empty, will be loaded via API when faculty is selected
    $fakulteti = Fakultet::all();
    return view('students.create', compact('nivoStudija', 'predmeti', 'fakulteti'));
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'ime' => 'required|string|max:255',
      'prezime' => 'required|string|max:255',
      'br_indexa' => 'required|string|max:20|unique:studenti',
      'datum_rodjenja' => 'required|date',
      'telefon' => 'required|string|max:255',
      'email' => 'required|email|max:255|unique:studenti,email',
      'godina_studija' => 'required|integer',
      'jmbg' => 'required|string|size:13|unique:studenti,jmbg',
      'nivo_studija_id' => [
          'required',
          'exists:nivo_studija,id',
          function ($attribute, $value, $fail) use ($request) {
              if ($request->godina_studija > 4) {
                  $master = NivoStudija::where('naziv', 'Master')->first();
                  if (!$master || $value != $master->id) {
                      $fail('Za godinu studija veću od 4, nivo studija mora biti Master.');
                  }
              }
          },
      ],
      'pol' => 'required|string|in:musko,zensko',
      'fakultet_id' => 'required|exists:fakulteti,id',
      'predmeti' => 'array',
      'predmeti.*' => 'array', // Each item in predmeti should be an array (e.g., ['grade' => 7])
      'predmeti.*.grade' => 'required|integer|min:6|max:10', // Validate grades if present
      'status' => 'required|in:mobilnost,prepis',
    ]);

    $student = Student::create($validated);

    if ($request->has('predmeti')) {
      $syncData = [];
      foreach ($request->predmeti as $id => $data) {
        // Ensure the ID itself is a valid subject ID before syncing
        if (Predmet::where('id', $id)->exists()) {
          $syncData[$id] = ['grade' => $data['grade'] ?? null];
        }
      }
      $student->predmeti()->sync($syncData);
    }

    if ($request->has('fakultet_id')) {
      $student->fakulteti()->sync([$request->fakultet_id]);
    }

    return redirect()->route('students.index')
      ->with('success', 'Student created successfully!');
  }

  public function edit($id)
  {
    $student = Student::with(['predmeti', 'fakulteti'])->findOrFail($id);
    $nivoStudija = NivoStudija::all();
    
    $studentFaculty = $student->fakulteti->first();
    if ($studentFaculty) {
        $predmeti = Predmet::where('fakultet_id', $studentFaculty->id)->get();
    } else {
        $predmeti = collect();
    }
    
    $fakulteti = Fakultet::all();
    return view('students.edit', compact('student', 'nivoStudija', 'predmeti', 'fakulteti'));
  }

  public function update(Request $request, $id)
  {
    $student = Student::findOrFail($id);

    $validated = $request->validate([
      'ime' => 'required|string|max:255',
      'prezime' => 'required|string|max:255',
      'br_indexa' => 'required|string|max:20|unique:studenti,br_indexa,' . $id,
      'datum_rodjenja' => 'required|date',
      'telefon' => 'required|string|max:255',
      'email' => 'required|email|max:255|unique:studenti,email,' . $id,
      'godina_studija' => 'required|integer',
      'jmbg' => 'required|string|size:13|unique:studenti,jmbg,' . $id,
      'nivo_studija_id' => [
        'required',
        'exists:nivo_studija,id',
        function ($attribute, $value, $fail) use ($request) {
            if ($request->godina_studija > 4) {
                $master = NivoStudija::where('naziv', 'Master')->first();
                if (!$master || $value != $master->id) {
                    $fail('Za godinu studija veću od 4, nivo studija mora biti Master.');
                }
            }
        },
      ],
      'pol' => 'required|string|in:musko,zensko',
      'fakultet_id' => 'required|exists:fakulteti,id',
      'predmeti' => 'array',
      'predmeti.*' => 'array', // Each item in predmeti should be an array (e.g., ['grade' => 7])
      'predmeti.*.grade' => 'required|integer|min:6|max:10', // Validate grades if present
      'status' => 'required|in:mobilnost,prepis',
    ]);

    $student->update($validated);

    if ($request->has('predmeti')) {
      $syncData = [];
      foreach ($request->predmeti as $id => $data) {
        if (!is_array($data)) {
          // If $data is not an array, assume it's just the subject ID
          // and set grade to null. This handles cases where only subject IDs are sent.
          $syncData[$data] = ['grade' => null];
        } else {
          // If $data is an array, assume it contains 'grade'
          $syncData[$id] = ['grade' => $data['grade'] ?? null];
        }
      }
      $student->predmeti()->sync($syncData);
    } else {
      $student->predmeti()->detach();
    }

    if ($request->has('fakultet_id')) {
      $student->fakulteti()->sync([$request->fakultet_id]);
    }

    return redirect()->route('students.index')
      ->with('success', 'Student uspješno izmijenjen!');
  }

  public function destroy($id)
  {
    $student = Student::findOrFail($id);
    
    if ($student->mappingRequests) {
        foreach ($student->mappingRequests as $req) {
            $req->subjects()->delete();
            $req->delete();
        }
    }


    
    $student->predmeti()->detach();
    $student->fakulteti()->detach();

    $student->delete();

    return redirect()->route('students.index')
      ->with('success', 'Student deleted successfully!');
  }

  public function uploadTor(Request $request, int $id, TorImportService $importService)
  {
      $request->validate([
          'tor_file' => 'required|file|mimes:doc,docx',
          'language' => 'required|in:Srpski,Engleski',
      ]);

      $student = Student::with('fakulteti')->findOrFail($id);
      
      if ($student->fakulteti->first()?->naziv !== 'FIT') {
          return back()->with('error', 'This feature is only available for FIT students.');
      }

      try {
          $file = $request->file('tor_file');
          $path = $file->path();
          
          $courses = $importService->loadCoursesWithGrades($path);
          $language = $request->input('language');

          $syncData = [];
          $matchedCount = 0;
          $totalCount = count($courses);

          $facultyId = $student->fakulteti->first()->id;
          $availableSubjects = Predmet::where('fakultet_id', $facultyId)->get();

          Log::info("Tor Import Debug: Loaded $totalCount subjects from file.");
          Log::info("Tor Import Debug: Available DB subjects count: " . $availableSubjects->count());

          $missedSubjects = [];

          foreach ($courses as $courseData) {
              $courseName = trim($courseData['Course']);
              $grade = $this->mapGrade($courseData['Grade']);

              $matchedSubject = null;

              if ($language === 'Engleski') {
                  $matchedSubject = $availableSubjects->first(function ($subject) use ($courseName) {
                      // Debug log comparison for first few or failing ones
                      return mb_strtolower(trim($subject->naziv_engleski ?? '')) === mb_strtolower($courseName);
                  });
              } else {
                   $matchedSubject = $availableSubjects->first(function ($subject) use ($courseName) {
                      return mb_strtolower(trim($subject->naziv)) === mb_strtolower($courseName);
                   });
              }

              if ($matchedSubject) {
                  $syncData[$matchedSubject->id] = ['grade' => $grade];
                  $matchedCount++;
              } else {
                  $missedSubjects[] = $courseName;
                  Log::warning("Tor Import Debug: No match found for '$courseName' (Lang: $language)");
              }
          }

          Log::info("Tor Import Debug: Total matched: $matchedCount. Missed: " . implode(', ', $missedSubjects));

          if (!empty($syncData)) {
              $student->predmeti()->syncWithoutDetaching($syncData);
          }

          $msg = "ToR processed. Matched $matchedCount out of $totalCount courses.";
          if (count($missedSubjects) > 0) {
              $msg .= " Missed: " . count($missedSubjects) . ". Check logs for details.";
          }

          return redirect()->route('students.edit', $student->id)
              ->with('success', $msg);

      } catch (\Exception $e) {
          Log::error('ToR Upload Error: ' . $e->getMessage());
          return back()->with('error', 'Failed to process ToR file: ' . $e->getMessage());
      }
  }

  public function parseTor(Request $request, TorImportService $importService)
  {
      $request->validate([
          'tor_file' => 'required|file|mimes:doc,docx',
          'language' => 'required|in:Srpski,Engleski',
          'fakultet_id' => 'required|exists:fakulteti,id',
      ]);

      try {
          $file = $request->file('tor_file');
          $path = $file->path();
          
          $courses = $importService->loadCoursesWithGrades($path);
          $language = $request->input('language');
          $facultyId = $request->input('fakultet_id');

          $matchedCount = 0;
          $totalCount = count($courses);

          $availableSubjects = Predmet::where('fakultet_id', $facultyId)->get();

          $results = [];
          $missedSubjects = [];

          foreach ($courses as $courseData) {
              $courseName = trim($courseData['Course']);
              $grade = $this->mapGrade($courseData['Grade']);

              $matchedSubject = null;

              if ($language === 'Engleski') {
                  $matchedSubject = $availableSubjects->first(function ($subject) use ($courseName) {
                      return mb_strtolower(trim($subject->naziv_engleski ?? '')) === mb_strtolower($courseName);
                  });
              } else {
                   $matchedSubject = $availableSubjects->first(function ($subject) use ($courseName) {
                      return mb_strtolower(trim($subject->naziv)) === mb_strtolower($courseName);
                   });
              }

              if ($matchedSubject) {
                  // Structure compatible with subject-selector
                  $results[] = [
                      'id' => $matchedSubject->id,
                      'naziv' => $matchedSubject->naziv,
                      'semestar' => $matchedSubject->semestar,
                      'ects' => $matchedSubject->ects,
                      'pivot' => ['grade' => $grade] // Simulate pivot structure
                  ];
                  $matchedCount++;
              } else {
                  $missedSubjects[] = $courseName;
              }
          }

          return response()->json([
              'success' => true,
              'matched' => $results,
              'message' => "ToR processed. Matched $matchedCount out of $totalCount courses.",
              'missed' => $missedSubjects
          ]);

      } catch (\Exception $e) {
          Log::error('ToR Parse Error: ' . $e->getMessage());
          return response()->json([
              'success' => false,
              'message' => 'Failed to process ToR file: ' . $e->getMessage()
          ], 500);
      }
  }

  private function mapGrade($gradeLetter)
  {
      $map = [
          'A' => 10,
          'B' => 9,
          'C' => 8,
          'D' => 7,
          'E' => 6,
          'F' => 5, 
      ];

      return $map[strtoupper(trim($gradeLetter))] ?? null;
  }
}
