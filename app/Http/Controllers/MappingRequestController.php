<?php

namespace App\Http\Controllers;

use App\Models\MappingRequest;
use App\Models\MappingRequestSubject;
use App\Models\Predmet;
use Illuminate\Http\Request;

class MappingRequestController extends Controller
{
    public function show($id)
    {
        $mappingRequest = MappingRequest::with(['fakultet', 'subjects.straniPredmet', 'subjects.fitPredmet'])->findOrFail($id);
        
        if (!$mappingRequest->subjects()->where('professor_id', auth()->id())->exists()) {
             abort(403);
        }

        $professorSubjects = auth()->user()->predmeti;

        return view('mapping_request.show', compact('mappingRequest', 'professorSubjects'));
    }

    public function update(Request $request, $id)
    {
        $mappingRequest = MappingRequest::findOrFail($id);

        if (!$mappingRequest->subjects()->where('professor_id', auth()->id())->exists()) {
            abort(403);
        }

        if (in_array($mappingRequest->status, ['accepted', 'rejected'])) {
            return response()->json(['message' => 'Zahtjev je finalizovan i ne može se mijenjati.'], 403);
        }

        $request->validate([
            'mappings' => 'array',
            'mappings.*.request_subject_id' => 'required|exists:mapping_request_subjects,id',
            'mappings.*.fit_predmet_id' => 'required|exists:predmeti,id',
            'comment' => 'nullable|string'
        ]);

        // Handle Comment
        if ($request->has('comment')) {
            \App\Models\ProfessorRequestComment::updateOrCreate(
                [
                    'mapping_request_id' => $mappingRequest->id,
                    'professor_id' => auth()->id(),
                ],
                [
                    'comment' => $request->comment
                ]
            );
        }

        $mySubjects = MappingRequestSubject::where('mapping_request_id', $mappingRequest->id)
            ->where('professor_id', auth()->id())
            ->get();
        
        $inputMappings = collect($request->input('mappings', []))->keyBy('request_subject_id');

        foreach ($mySubjects as $subject) {
            if ($inputMappings->has($subject->id)) {
                $fitPredmetId = $inputMappings->get($subject->id)['fit_predmet_id'];
                
                $subject->update([
                    'fit_predmet_id' => $fitPredmetId,
                    'is_rejected' => false
                ]);

                if ($fitPredmetId) {
                    MappingRequestSubject::where('professor_id', auth()->id())
                        ->where('strani_predmet_id', $subject->strani_predmet_id)
                        ->where('id', '!=', $subject->id) // Exclude current record
                        ->whereHas('mappingRequest', function ($query) {
                            $query->where('status', 'pending');
                        })
                        ->update([
                            'fit_predmet_id' => $fitPredmetId,
                            'is_rejected' => false
                        ]);
                }

            } else {
                 $subject->update([
                    'fit_predmet_id' => null,
                    'is_rejected' => true
                ]);
            }
        }

        session()->flash('success', 'Povezani predmeti i komentar uspješno sačuvani.');

        return response()->json(['message' => 'Povezani predmeti i komentar uspješno sačuvani.']);
    }
}
