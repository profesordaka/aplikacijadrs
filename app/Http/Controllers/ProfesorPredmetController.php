<?php

namespace App\Http\Controllers;

use App\Models\Predmet;
use App\Models\User;
use Illuminate\Http\Request;

class ProfesorPredmetController extends Controller
{
    public function index($userId)
    {
        $user = User::findOrFail($userId);
        
        if ($user->type != 1) {
             return redirect()->route('users.index')->with('error', 'User is not a professor');
        }

        $predmeti = \App\Models\Predmet::select('id', 'naziv', 'ects')->get();

        return view('users.subjects', compact('user', 'predmeti'));
    }

    public function store(Request $request, $userId)
    {
        $user = User::findOrFail($userId);
        
        $request->validate([
            'predmet_id' => 'required|exists:predmeti,id'
        ]);

        if (!$user->predmeti()->where('predmet_id', $request->predmet_id)->exists()) {
            $user->predmeti()->attach($request->predmet_id);
        }

        return redirect()->route('users.subjects.index', $userId)->with('success', 'Predmet uspješno dodat');
    }

    public function destroy($userId, $predmetId)
    {
        $user = User::findOrFail($userId);
        $user->predmeti()->detach($predmetId);

        return redirect()->route('users.subjects.index', $userId)->with('success', 'Predmet uspješno uklonjen');
    }
}
