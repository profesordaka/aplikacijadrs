<?php

namespace App\Http\Controllers;

use App\Models\Fakultet;
use App\Models\Predmet;
use App\Models\Prepis;
use App\Models\PrepisAgreement;
use App\Models\Student;
use Illuminate\Http\Request;

class PrepisController extends Controller
{
    public function index()
    {
        $prepisi = Prepis::with(['student', 'fakultet'])->latest()->get();
        $mappingRequests = \App\Models\MappingRequest::with(['professor', 'student', 'subjects.straniPredmet', 'subjects.fitPredmet'])
            ->latest()
            ->get();
            
        return view('prepis.index', compact('prepisi', 'mappingRequests'));
    }



    public function edit($id)
    {
        $prepis = Prepis::with(['student', 'fakultet', 'agreements.fitPredmet', 'agreements.straniPredmet'])->findOrFail($id);
        $studenti = Student::all();
        $fakulteti = Fakultet::all();
        $predmeti = Predmet::select('id', 'naziv', 'ects', 'fakultet_id')->get();

        $existingAgreements = PrepisAgreement::select('fit_predmet_id', 'strani_predmet_id')
            ->distinct()
            ->get()
            ->groupBy('fit_predmet_id')
            ->map(function ($items) {
                return $items->pluck('strani_predmet_id');
            });

        $existingAgreementsForeign = PrepisAgreement::select('fit_predmet_id', 'strani_predmet_id')
            ->distinct()
            ->get()
            ->groupBy('strani_predmet_id')
            ->map(function ($items) {
                return $items->pluck('fit_predmet_id');
            });

        $mappedSubjects = \App\Models\MappingRequestSubject::whereNotNull('fit_predmet_id')
            ->get()
            ->pluck('fit_predmet_id', 'strani_predmet_id');

        $mappedSubjectsReverse = \App\Models\MappingRequestSubject::whereNotNull('fit_predmet_id')
            ->get()
            ->groupBy('fit_predmet_id')
            ->map(function ($items) {
                return $items->pluck('strani_predmet_id');
            });

        return view('prepis.edit', compact('prepis', 'studenti', 'fakulteti', 'predmeti', 'existingAgreements', 'existingAgreementsForeign', 'mappedSubjects', 'mappedSubjectsReverse'));
    }

    public function update(Request $request, $id)
    {
        $prepis = Prepis::findOrFail($id);

        $request->validate([
            'student_id' => 'required|exists:studenti,id',
            'fakultet_id' => 'required|exists:fakulteti,id',
            'datum' => 'required|date',
            'agreements' => 'nullable|array',
            'agreements.*.fit_predmet_id' => 'required|exists:predmeti,id',
            'agreements.*.strani_predmet_id' => 'required|exists:predmeti,id',
        ]);

        $prepis->update([
            'student_id' => $request->student_id,
            'fakultet_id' => $request->fakultet_id,
            'datum' => $request->datum,
        ]);

        $prepis->agreements()->delete();

        if ($request->has('agreements')) {
            foreach ($request->agreements as $agreementData) {
                PrepisAgreement::create([
                    'prepis_id' => $prepis->id,
                    'fit_predmet_id' => $agreementData['fit_predmet_id'],
                    'strani_predmet_id' => $agreementData['strani_predmet_id'],
                    'status' => 'u procesu',
                ]);
            }
        }

        return redirect()->route('prepis.index')->with('success', 'Prepis updated successfully.');
    }

    public function destroy($id)
    {
        $prepis = Prepis::findOrFail($id);
        $prepis->agreements()->delete();
        $prepis->delete();
        return redirect()->route('prepis.index')->with('success', 'Prepis deleted successfully.');
    }

    public function show($id)
    {
        $prepis = Prepis::with(['student', 'fakultet', 'agreements.fitPredmet', 'agreements.straniPredmet'])->findOrFail($id);
        return view('prepis.show', compact('prepis'));
    }


    public function match()
    {
        $professors = \App\Models\User::where('type', 1)->get();
        $students = Student::whereHas('predmeti')->get();
        
        $previousMatches = \App\Models\MappingRequestSubject::whereNotNull('fit_predmet_id')
            ->with(['professor', 'fitPredmet'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->unique('strani_predmet_id')
            ->mapWithKeys(function ($item) {
                return [$item->strani_predmet_id => [
                    'professor_id' => $item->professor_id,
                    'professor_name' => $item->professor->name,
                    'fit_predmet_id' => $item->fit_predmet_id,
                    'fit_predmet_name' => $item->fitPredmet->naziv,
                    'date' => $item->created_at->format('d.m.Y'),
                ]];
            });

        // Fetch ALL pending matches grouped by subject ID
        // This allows us to see if a subject is pending with a professor for ANY student
        $globalPendingMatches = \App\Models\MappingRequestSubject::whereHas('mappingRequest', function($q) {
                $q->where('status', 'pending');
            })
            ->where(function($q) {
                $q->where('is_rejected', false)
                  ->orWhereNull('is_rejected');
            })
            ->with(['professor'])
            ->get()
            ->unique('strani_predmet_id')
            ->mapWithKeys(function($item) {
                return [$item->strani_predmet_id => [
                    'professor_id' => $item->professor_id,
                    'professor_name' => $item->professor->name,
                    'status' => 'pending'
                ]];
            });
        
        return view('prepis.match', compact('professors', 'students', 'previousMatches', 'globalPendingMatches'));
    }

    public function storeMatch(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:studenti,id',
            'matches' => 'required|array',
            'matches.*.professor_id' => 'required|exists:users,id',
            'matches.*.subject_id' => 'required|exists:predmeti,id',
            'matches.*.fit_predmet_id' => 'nullable|exists:predmeti,id',
        ]);
        
       
        $firstMatch = $request->matches[0];
        $firstSubject = \App\Models\Predmet::find($firstMatch['subject_id']);
        $fakultetId = $firstSubject ? $firstSubject->fakultet_id : null;

        $mappingRequest = \App\Models\MappingRequest::create([
            'professor_id' => null, // No single professor anymore
            'student_id' => $request->student_id,
            'fakultet_id' => $fakultetId,
            'status' => 'pending',
        ]);

        foreach ($request->matches as $match) {
            \App\Models\MappingRequestSubject::create([
                'mapping_request_id' => $mappingRequest->id,
                'strani_predmet_id' => $match['subject_id'],
                'professor_id' => $match['professor_id'], // Assign professor to the subject
                'fit_predmet_id' => $match['fit_predmet_id'] ?? null,
            ]);
        }


        return response()->json(['message' => 'Zahtjev za mačovanje poslat uspješno.']);
    }

    public function getStudentSubjects($studentId)
    {
        $student = Student::with('predmeti')->findOrFail($studentId);
        return response()->json($student->predmeti);
    }

    public function showMappingRequest($id)
    {
        $mappingRequest = \App\Models\MappingRequest::with(['professor', 'student', 'subjects.straniPredmet', 'subjects.fitPredmet'])->findOrFail($id);
        $fitSubjects = Predmet::where('fakultet_id', 1)->get(); 
        
        // Fetch existing foreign subjects in this request to exclude them
        // Exclude rejected ones so they can be re-added/updated
        $existingSubjectIds = $mappingRequest->subjects()
            ->where(function($q) {
                $q->where('is_rejected', false)
                  ->orWhereNull('is_rejected');
            })
            ->pluck('strani_predmet_id')
            ->toArray();
        
        // Fetch ALL subjects for this student that are NOT already in this request
        // Ensure to fetch subjects that belong to the mapping request's faculty? 
        // Or just all student's subjects? Usually mobility is one Uni.
        // Let's filter by student's enrolled subjects.
        // Assuming student->predmeti (many-to-many) exists.
        
        $studentSubjects = $mappingRequest->student->predmeti()
            ->whereNotIn('predmeti.id', $existingSubjectIds)
            // Optional: Filter by faculty if needed, but student might have subjects from multiple?
            // For now, let's filter by the faculty of the request to be safe/consistent?
            // If the request is for "University of X", we only want subjects from there.
            ->where('fakultet_id', $mappingRequest->fakultet_id)
            ->get();

        // Fetch professors for the dropdown (needed for drag-and-drop)
        $professors = \App\Models\User::where('type', 1)->orderBy('name')->get();

        return view('prepis.mapping_request_show', compact('mappingRequest', 'fitSubjects', 'studentSubjects', 'professors'));
    }

    public function addMappingRequestSubject(Request $request, $id)
    {
        // Legacy single add - can keep or remove. Keeping for robustnes if needed via API, but UI will use Bulk.
        // Refactoring to just use Bulk or keep as fallback.
        // Let's keep it.
        $mappingRequest = \App\Models\MappingRequest::findOrFail($id);
        // ... (existing logic)
        $request->validate([
            'strani_predmet_id' => 'required|exists:predmeti,id',
            'professor_id' => 'required|exists:users,id',
        ]);
        
        if ($mappingRequest->subjects()->where('strani_predmet_id', $request->strani_predmet_id)->exists()) {
             return redirect()->back()->with('error', 'Subject is already in this request.');
        }

        \App\Models\MappingRequestSubject::create([
            'mapping_request_id' => $mappingRequest->id,
            'strani_predmet_id' => $request->strani_predmet_id,
            'professor_id' => $request->professor_id,
        ]);

        return redirect()->back()->with('success', 'Subject added.');
    }

    public function storeBulkSubjects(Request $request, $id)
    {
        $mappingRequest = \App\Models\MappingRequest::findOrFail($id);

        $request->validate([
            'matches' => 'required|array',
            'matches.*.professor_id' => 'required|exists:users,id',
            'matches.*.subject_id' => 'required|exists:predmeti,id',
        ]);

        $count = 0;
        $count = 0;
        foreach ($request->matches as $match) {
            $existingSubject = $mappingRequest->subjects()->where('strani_predmet_id', $match['subject_id'])->first();

            if ($existingSubject) {
                if ($existingSubject->is_rejected) {
                    // Reactivate rejected subject
                    $existingSubject->update([
                        'professor_id' => $match['professor_id'],
                        'is_rejected' => false,
                        'fit_predmet_id' => null, // Reset match
                    ]);
                    $count++;
                }
                // Else: already exists and active, skip
            } else {
                // Create new
                \App\Models\MappingRequestSubject::create([
                    'mapping_request_id' => $mappingRequest->id,
                    'strani_predmet_id' => $match['subject_id'],
                    'professor_id' => $match['professor_id'],
                ]);
                $count++;
            }

            // Propagate to other pending rejected requests
            \App\Models\MappingRequestSubject::where('strani_predmet_id', $match['subject_id'])
                ->where('id', '!=', $existingSubject ? $existingSubject->id : 0) // Exclude current
                ->where('is_rejected', true)
                ->whereHas('mappingRequest', function($q) {
                    $q->where('status', 'pending');
                })
                ->update([
                    'professor_id' => $match['professor_id'],
                    'is_rejected' => false,
                    'fit_predmet_id' => null,
                ]);
        }

        return response()->json(['message' => "$count subjects added successfully."]);
    }

    public function updateMappingRequestSubject(Request $request, $id)
    {
        $subject = \App\Models\MappingRequestSubject::findOrFail($id);
        
        $request->validate([
            'fit_predmet_id' => 'nullable|exists:predmeti,id',
        ]);

        $subject->update(['fit_predmet_id' => $request->fit_predmet_id]);

        return redirect()->back()->with('success', 'Subject mapping updated.');
    }

    public function removeMappingRequestSubject($id)
    {
         $subject = \App\Models\MappingRequestSubject::findOrFail($id);
         $subject->delete();
         return redirect()->back()->with('success', 'Subject removed from request.');
    }

    public function acceptMappingRequest($id)
    {
        $mappingRequest = \App\Models\MappingRequest::with('subjects')->findOrFail($id);
        
        if (!in_array($mappingRequest->status, ['pending', 'completed'])) {
            return redirect()->back()->with('error', 'Request is not pending.');
        }

        // Update Student Faculty to FIT (ID 2) and Sync Subjects
        $student = $mappingRequest->student;
        // Student has many-to-many relationship with Faculty
        $student->fakulteti()->sync([2]);

        $newSubjectIds = $mappingRequest->subjects()
            ->whereNotNull('fit_predmet_id')
            ->pluck('fit_predmet_id')
            ->toArray();
            
        $student->predmeti()->sync($newSubjectIds);

        // Create Prepis
        $prepis = Prepis::create([
            'student_id' => $mappingRequest->student_id,
            'fakultet_id' => 2, // Ensure Prepis is also linked to FIT
            'datum' => now(), 
            'status' => 'odobren', // Accepted
        ]);

        foreach ($mappingRequest->subjects as $subject) {
            // Only add agreements if they are matched (have fit_predmet_id)
            // Or should we add all? Unmatched ones can't be added to PrepisAgreement usually without fit_predmet_id?
            // PrepisAgreement requires fit_predmet_id and strani_predmet_id?
            // Let's check migration of PrepisAgreement.
            // If fit_predmet_id is nullable mostly no?
            // Assuming we only add matched ones.
            
            if ($subject->fit_predmet_id) {
                PrepisAgreement::create([
                    'prepis_id' => $prepis->id,
                    'fit_predmet_id' => $subject->fit_predmet_id,
                    'strani_predmet_id' => $subject->strani_predmet_id,
                    'status' => 'odobren',
                ]);
            }
        }

        $mappingRequest->update(['status' => 'accepted']);

        return redirect()->back()->with('success', 'Zahtjev za mačovanje prihvaćen i prepis kreiran.');
    }

    public function rejectMappingRequest($id)
    {
        $mappingRequest = \App\Models\MappingRequest::findOrFail($id);
        
        if (!in_array($mappingRequest->status, ['pending', 'completed'])) {
            return redirect()->back()->with('error', 'Zahtjev nije na čekanju.');
        }

        $mappingRequest->update(['status' => 'rejected']);

        return redirect()->back()->with('success', 'Zahtjev za mačovanje nije prihvaćen.');
    }

    public function destroyMappingRequest($id)
    {
        $mappingRequest = \App\Models\MappingRequest::findOrFail($id);
        $mappingRequest->delete();
        return redirect()->back()->with('success', 'Zahtjev za mačovanje obrisan.');
    }
}
