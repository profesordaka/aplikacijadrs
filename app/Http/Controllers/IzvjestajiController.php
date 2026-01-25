<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\Prepis;
use App\Models\Mobilnost;
use App\Models\NivoStudija;
use App\Models\Fakultet;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;
use App\Exports\PrepisiExport;
use App\Exports\MobilnostExport;

class IzvjestajiController extends Controller
{
    public function index(Request $request)
    {
        $filterFakultet = $request->get('fakultet');
        $filterDrzava = $request->get('drzava');
        $filterYear = $request->get('year');
        $filterNivo = $request->get('nivo');

        $prepisRaw = Prepis::with('fakultet.univerzitet', 'student')
            ->when($filterFakultet, function($q) use ($filterFakultet) { return $q->where('fakultet_id', $filterFakultet); })
            ->when($filterYear, function($q) use ($filterYear) { return $q->whereYear('datum', $filterYear); })
            ->get();
        $prepisiAgg = [];
        foreach ($prepisRaw as $p) {
            $year = \Carbon\Carbon::parse($p->datum)->format('Y');
            $fakultetNaziv = $p->fakultet->naziv ?? 'Nepoznato';
            $drzava = $p->fakultet && $p->fakultet->univerzitet ? $p->fakultet->univerzitet->drzava : 'Nepoznato';
            $key = $year . '|' . $fakultetNaziv . '|' . $drzava;
            if (!isset($prepisiAgg[$key])) {
                $prepisiAgg[$key] = ['year' => $year, 'fakultet' => $fakultetNaziv, 'drzava' => $drzava, 'total' => 0, 'musko' => 0, 'zensko' => 0];
            }
            $prepisiAgg[$key]['total']++;
            // Broji po polu
            if ($p->student) {
                if ($p->student->pol === 'musko') {
                    $prepisiAgg[$key]['musko']++;
                } else {
                    $prepisiAgg[$key]['zensko']++;
                }
            }
        }
        $prepisi = collect($prepisiAgg)->sortBy(function($x) { return $x['year'] . $x['fakultet']; })->values()->map(function($x) { 
            $x['procenat_musko'] = $x['total'] > 0 ? round(($x['musko'] / $x['total']) * 100, 2) : 0;
            $x['procenat_zensko'] = $x['total'] > 0 ? round(($x['zensko'] / $x['total']) * 100, 2) : 0;
            return (object)$x; 
        });

        // prepisi po polu - sa listom studenata
        $prepisiGenderData = collect();
        $prepisYearAgg = []; // Za godišnje podatke
        $genderCounts = ['musko' => 0, 'zensko' => 0]; // Broji jedinstvene studente
        $seenStudents = ['musko' => [], 'zensko' => []]; // Prati koje studente smo videli
        
        foreach ($prepisRaw as $p) {
            $pol = $p->student ? $p->student->pol : null;
            if ($pol && $p->student) {
                $studentId = $p->student->id;
                $studentName = $p->student->ime . ' ' . $p->student->prezime;
                $polKey = $pol === 'musko' ? 'musko' : 'zensko';
                
                // Po polu - broji samo prvi put kada vidimo ovog studenta
                $existing = $prepisiGenderData->firstWhere('pol', $pol);
                if (!$existing) {
                    $prepisiGenderData->push((object)[
                        'pol' => $pol,
                        'label' => $pol === 'musko' ? 'Muško' : 'Žensko',
                        'total' => 0,
                        'students' => []
                    ]);
                    $existing = $prepisiGenderData->last();
                }
                
                // Dodaj u pol listu samo ako nije već dodan
                if (!in_array($studentName, $existing->students)) {
                    $existing->students[] = $studentName;
                    $existing->total++;
                }
                
                // Po godini
                $year = \Carbon\Carbon::parse($p->datum)->format('Y');
                if (!isset($prepisYearAgg[$year])) {
                    $prepisYearAgg[$year] = ['musko' => [], 'zensko' => []];
                }
                
                // Dodaj u godinu ako nije već dodan
                if (!in_array($studentName, $prepisYearAgg[$year][$polKey])) {
                    $prepisYearAgg[$year][$polKey][] = $studentName;
                }
            }
        }
        
        // Konvertuj godišnje u objekat
        $prepisYearData = (object)[];
        foreach ($prepisYearAgg as $year => $data) {
            $prepisYearData->$year = (object)[
                'year' => $year,
                'students_musko' => $data['musko'],
                'students_zensko' => $data['zensko']
            ];
        }

        // available nivo options for filter
        $nivoOptions = NivoStudija::orderBy('id')->get();

        // Build detailed mobilnosti stats using PHP to be DB-agnostic
        $mobilnostiRaw = Mobilnost::with('student.nivoStudija', 'fakultet.univerzitet')
->when($filterDrzava, fn($q) => $q->whereHas('fakultet.univerzitet', fn($subq) => $subq->where('drzava', $filterDrzava)))

            ->when($filterYear, function($q) use ($filterYear) {
                return $q->whereYear('datum_pocetka', $filterYear);
            })
            ->when($filterFakultet, function($q) use ($filterFakultet) {
                return $q->where('fakultet_id', $filterFakultet);
            })
            ->when($filterNivo, function($q) use ($filterNivo) {
                return $q->whereHas('student', function($subq) use ($filterNivo) {
                    $subq->where('nivo_studija_id', $filterNivo);
                });
            })
            ->get();

        $mobilnostiGenderData = $mobilnostiRaw
    ->filter(fn($m) => $m->student) // samo ako student postoji
    ->groupBy(fn($m) => $m->student->pol) // grupiši po polu
    ->map(function ($items, $pol) {

        return (object) [
            'label' => $pol === 'musko' ? 'Muško' : 'Žensko',
            'pol' => $pol,
            'total' => $items->count(),
            'students' => $items
                ->groupBy(fn($m) => $m->student->id) // grupiši po student ID-u
                ->map(function($studentItems) {
                    $student = $studentItems->first()->student;
                    $count = $studentItems->count();
                    $displayName = $student->ime . ' ' . $student->prezime;
                    // Ako je student više puta na mobilnosti, dodaj broj u zagradama
                    return $count > 1 ? $displayName . ' (' . $count . ')' : $displayName;
                })
                ->values()
        ];
    })
    ->values();


            $mobilnostiAgg = [];
        $nivoAgg = ['Osnovne' => [], 'Master' => []];
        $yearAgg = [];
        
        foreach ($mobilnostiRaw as $m) {
            $date = $m->datum_pocetka ?? $m->created_at ?? null;
            if (!$date) continue;

            $year = \Carbon\Carbon::parse($date)->format('Y');
            // Include drzava in the grouping key
            $drzava = $m->fakultet && $m->fakultet->univerzitet ? $m->fakultet->univerzitet->drzava : 'Nepoznato';
            $key = $year . '|' . $drzava;
            
            if (!isset($mobilnostiAgg[$key])) {
                $mobilnostiAgg[$key] = [
                    'year' => $year,
                    'drzava' => $drzava,
                    'total' => 0,
                    'musko' => 0,
                    'zensko' => 0,
                    'master' => 0,
                    'osnovne' => 0,
                    'students' => []
                ];
            }

            $mobilnostiAgg[$key]['total']++;

            $student = $m->student;
            if ($student) {
                $studentName = $student->ime . ' ' . $student->prezime;
                
                // pol: string 'musko' or 'zensko'
                if ($student->pol === 'musko') {
                    $mobilnostiAgg[$key]['musko']++;
                } else {
                    $mobilnostiAgg[$key]['zensko']++;
                }

                $nivo = $student->nivoStudija->naziv ?? null;
                $nivoLabel = 'Osnovne';
                if ($nivo && mb_stripos($nivo, 'master') !== false) {
                    $mobilnostiAgg[$key]['master']++;
                    $nivoLabel = 'Master';
                } else {
                    $mobilnostiAgg[$key]['osnovne']++;
                }
                
                // Prikupljaj studente
                if (!in_array($studentName, $mobilnostiAgg[$key]['students'])) {
                    $mobilnostiAgg[$key]['students'][] = $studentName;
                }
                
                // Za nivo
                $nivoAgg[$nivoLabel][$student->id] = ($nivoAgg[$nivoLabel][$student->id] ?? 0) + 1;
                
                // Za godišnje
                if (!isset($yearAgg[$year])) {
                    $yearAgg[$year] = [];
                }
                $yearAgg[$year][$student->id] = ($yearAgg[$year][$student->id] ?? 0) + 1;
            }
        }

        // Convert to sorted array
        $mobilnosti = collect($mobilnostiAgg)->sortBy(function($x) { return $x['year'] . $x['drzava']; })->values()->map(function ($r) {
            $r['procenat_musko'] = $r['total'] > 0 ? round(($r['musko'] / $r['total']) * 100, 2) : 0;
            $r['procenat_zensko'] = $r['total'] > 0 ? round(($r['zensko'] / $r['total']) * 100, 2) : 0;
            return (object) $r;
        });

        // Generiši mobilnostiByNivo sa studentima
        $mobilnostiByNivo = [];
        foreach (['Osnovne', 'Master'] as $label) {
            $studentCounts = [];
            foreach ($nivoAgg[$label] as $studentId => $count) {
                $student = Student::find($studentId);
                if ($student) {
                    $studentName = $student->ime . ' ' . $student->prezime;
                    $displayName = $count > 1 ? $studentName . ' (' . $count . ')' : $studentName;
                    $studentCounts[] = $displayName;
                }
            }
            
            $mobilnostiByNivo[] = (object)[
                'label' => $label,
                'total' => $mobilnosti->sum($label === 'Master' ? 'master' : 'osnovne'),
                'students' => $studentCounts
            ];
        }

        // Generiši mobilnostiYearData sa studentima po godini
        $mobilnostiYearDataArr = [];
        foreach ($yearAgg as $year => $studentIds) {
            $studentsByMale = ['musko' => [], 'zensko' => []];
            
            foreach ($studentIds as $studentId => $count) {
                $student = Student::find($studentId);
                if ($student) {
                    $studentName = $student->ime . ' ' . $student->prezime;
                    $displayName = $count > 1 ? $studentName . ' (' . $count . ')' : $studentName;
                    
                    if ($student->pol === 'musko') {
                        $studentsByMale['musko'][] = $displayName;
                    } else {
                        $studentsByMale['zensko'][] = $displayName;
                    }
                }
            }
            
            // Dodaj godinu kao ključ - čuva se kao string ključ u nizu
            $mobilnostiYearDataArr[$year] = [
                'year' => $year,
                'students_musko' => $studentsByMale['musko'],
                'students_zensko' => $studentsByMale['zensko']
            ];
        }
        
        // Konvertuj u objekat - ovo će biti konvertovano u JSON objekat sa god kao ključevima
        $mobilnostiYearData = (object)$mobilnostiYearDataArr;

        $fakulteti = Fakultet::orderBy('naziv')->get();
        
        // Get all unique countries from universities
        $drzave = DB::table('univerziteti')
            ->distinct()
            ->orderBy('drzava')
            ->pluck('drzava');
        
return view('izvjestaji.index', compact(
    'prepisi',
    'mobilnosti',
    'nivoOptions',
    'fakulteti',
    'drzave',
    'filterYear',
    'filterFakultet',
    'filterDrzava',
    'filterNivo',
    'prepisiGenderData',
    'prepisYearData',
    'mobilnostiGenderData',
    'mobilnostiByNivo',
    'mobilnostiYearData'
));
    
}


    public function export(Request $request, $type)
    {
        // Gather data same as index
        $driver = DB::getDriverName();

        $filterNivo = $request->get('nivo');
        $filterYear = $request->get('year');
        $filterFakultet = $request->get('fakultet');
        $filterDrzava = $request->get('drzava');

        if ($driver === 'sqlite') {
            $yearSql = "strftime('%Y', created_at)";
        } elseif ($driver === 'pgsql') {
            $yearSql = "EXTRACT(YEAR FROM created_at)";
        } else {
            $yearSql = "YEAR(created_at)";
        }

        $studentsQuery = Student::selectRaw("$yearSql as year, COUNT(*) as total");
        if ($filterNivo) $studentsQuery->where('nivo_studija_id', $filterNivo);
        if ($filterYear) $studentsQuery->whereRaw("$yearSql = ?", [$filterYear]);
        $students = $studentsQuery->groupBy('year')->orderBy('year')->get();

        $prepisRaw = Prepis::with('fakultet.univerzitet', 'student')
            ->when($filterFakultet, function($q) use ($filterFakultet) { return $q->where('fakultet_id', $filterFakultet); })
            ->get();
        $prepisiAgg = [];
        foreach ($prepisRaw as $p) {
            $year = \Carbon\Carbon::parse($p->datum)->format('Y');
            $fakultetNaziv = $p->fakultet->naziv ?? 'Nepoznato';
            $drzava = $p->fakultet && $p->fakultet->univerzitet ? $p->fakultet->univerzitet->drzava : 'Nepoznato';
            $key = $year . '|' . $fakultetNaziv . '|' . $drzava;
            if (!isset($prepisiAgg[$key])) {
                $prepisiAgg[$key] = ['year' => $year, 'fakultet' => $fakultetNaziv, 'drzava' => $drzava, 'total' => 0, 'musko' => 0, 'zensko' => 0];
            }
            $prepisiAgg[$key]['total']++;
            // Broji po polu
            if ($p->student) {
                if ($p->student->pol === 'musko') {
                    $prepisiAgg[$key]['musko']++;
                } else {
                    $prepisiAgg[$key]['zensko']++;
                }
            }
        }
        $prepisi = collect($prepisiAgg)->sortBy(function($x) { return $x['year'] . $x['fakultet']; })->values()->map(function($x) { 
            $x['procenat_musko'] = $x['total'] > 0 ? round(($x['musko'] / $x['total']) * 100, 2) : 0;
            $x['procenat_zensko'] = $x['total'] > 0 ? round(($x['zensko'] / $x['total']) * 100, 2) : 0;
            return (object)$x; 
        });

        $mobilnostiRaw = Mobilnost::with('student.nivoStudija', 'fakultet.univerzitet')
            ->when($filterDrzava, function($q) use ($filterDrzava) {
                return $q->whereHas('fakultet.univerzitet', function($subq) use ($filterDrzava) {
                    $subq->where('drzava', $filterDrzava);
                });
            })
            ->when($filterYear, function($q) use ($filterYear) {
                return $q->whereYear('datum_pocetka', $filterYear);
            })
            ->when($filterFakultet, function($q) use ($filterFakultet) {
                return $q->where('fakultet_id', $filterFakultet);
            })
            ->when($filterNivo, function($q) use ($filterNivo) {
                return $q->whereHas('student', function($subq) use ($filterNivo) {
                    $subq->where('nivo_studija_id', $filterNivo);
                });
            })
            ->get();
        $mobilnostiAgg = [];
        foreach ($mobilnostiRaw as $m) {
            $date = $m->datum_pocetka ?? $m->created_at ?? null;
            if (!$date) continue;

            $year = \Carbon\Carbon::parse($date)->format('Y');
            $drzava = $m->fakultet && $m->fakultet->univerzitet ? $m->fakultet->univerzitet->drzava : 'Nepoznato';
            $key = $year . '|' . $drzava;
            
            if (!isset($mobilnostiAgg[$key])) {
                $mobilnostiAgg[$key] = [
                    'year' => $year,
                    'drzava' => $drzava,
                    'total' => 0,
                    'musko' => 0,
                    'zensko' => 0,
                    'master' => 0,
                    'osnovne' => 0,
                ];
            }

            $mobilnostiAgg[$key]['total']++;
            $student = $m->student;
            if ($student) {
                $pol = $student->pol; // 'musko' or 'zensko'
                if ($pol === 'musko') {
                    $mobilnostiAgg[$key]['musko']++;
                } else {
                    $mobilnostiAgg[$key]['zensko']++;
                }

                $nivo = $student->nivoStudija->naziv ?? null;
                if ($nivo && mb_stripos($nivo, 'master') !== false) {
                    $mobilnostiAgg[$key]['master']++;
                } else {
                    $mobilnostiAgg[$key]['osnovne']++;
                }
            }
        }

        $mobilnosti = collect($mobilnostiAgg)->sortBy(function($x) { return $x['year'] . $x['drzava']; })->values()->map(function ($r) {
            $r['procenat_musko'] = $r['total'] > 0 ? round(($r['musko'] / $r['total']) * 100, 2) : 0;
            $r['procenat_zensko'] = $r['total'] > 0 ? round(($r['zensko'] / $r['total']) * 100, 2) : 0;
            return (object) $r;
        });

        // Generate Excel
        if ($type === 'prepisi') {
            // --- PRIPREMI PODATKE ZA SUMMARY TABELU ---
            $prepisiSummaryData = $prepisi->map(function ($r) {
                return [
                    $r->year,
                    $r->fakultet,
                    $r->drzava,
                    $r->total,
                    $r->musko,
                    $r->zensko,
                    $r->procetat_musko,
                    $r->procetat_zensko,
                ];
            })->toArray();

            // --- PRIPREMI DETALJNE PODATKE O STUDENTIMA ---
            $prepisStudentsByYear = $prepisRaw
                ->groupBy(function ($p) {
                    $date = $p->datum ?? $p->created_at;
                    return \Carbon\Carbon::parse($date)->format('Y');
                })
                ->map(function ($prepisYear) {
                    return $prepisYear
                        ->map(function ($p) {
                            $student = $p->student;

                            return [
                                $student->ime ?? '',
                                $student->prezime ?? '',
                                $student->pol ?? '',
                                $student->nivoStudija->naziv ?? '',
                                $p->fakultet->naziv ?? '',
                                $p->fakultet && $p->fakultet->univerzitet
                                    ? $p->fakultet->univerzitet->naziv
                                    : '',
                                $p->fakultet && $p->fakultet->univerzitet
                                    ? $p->fakultet->univerzitet->drzava
                                    : '',
                                $p->datum ?? $p->created_at ?? '',
                            ];
                        })
                        ->values()
                        ->toArray();
                })
                ->toArray();

            return Excel::download(
                new PrepisiExport($prepisiSummaryData, $prepisStudentsByYear),
                'Prepisi.xlsx'
            );
        } elseif ($type === 'mobilnost') {
    // --- PRIPREMI PODATKE ZA SUMMARY TABELU ---
    $summaryData = $mobilnosti->map(function ($r) {
        return [
            $r->drzava,
            $r->year,
            $r->total,
            $r->musko,
            $r->zensko,
            round($r->procenat_musko),
            round($r->procenat_zensko),
            $r->master,
            $r->osnovne,
        ];
    })->toArray();

    // --- PRIPREMI DETALJNE PODATKE O STUDENTIMA ---
$studentsByYear = $mobilnostiRaw
    ->groupBy(function ($m) {
        $date = $m->datum_pocetka ?? $m->created_at;
        return \Carbon\Carbon::parse($date)->format('Y');
    })
    ->map(function ($mobilnostiYear) {

        return $mobilnostiYear
            ->map(function ($m) {

                $student = $m->student;

                return [
                    $student->ime ?? '',
                    $student->prezime ?? '',
                    $student->pol ?? '',
                    $student->nivoStudija->naziv ?? '',
                    $m->fakultet->naziv ?? '',
                    $m->fakultet && $m->fakultet->univerzitet
                        ? $m->fakultet->univerzitet->naziv
                        : '',
                    $m->fakultet && $m->fakultet->univerzitet
                        ? $m->fakultet->univerzitet->drzava
                        : '',
                    $m->datum_pocetka ?? $m->created_at ?? '',
                    $m->datum_kraja ?? '',
                ];
            })
            ->values()
            ->toArray();
    })
    ->toArray();

    // --- DOWNLOAD EXCELA SA NOVIM MobilnostExport ---
    return Excel::download(
    new MobilnostExport($summaryData, $studentsByYear),
    'Mobilnost.xlsx'
);
}

        return redirect()->back()->with('error', 'Nepoznat tip izvjestaja');
    }
}