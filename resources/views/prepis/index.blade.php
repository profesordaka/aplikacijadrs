<x-app-layout>
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-800 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Prepisi</h1>
            <div class="flex space-x-4">
                <a href="{{ route('prepis.match') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Dodaj prepis
                </a>
            </div>
        </div>



        <!-- Mapping Requests Table -->
        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 mt-8">
             <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Zahtjevi za prepis</h2>
                <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ count($mappingRequests) }} Ukupno</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Student</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Predmeti</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Akcije</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mappingRequests as $request)
                             <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex flex-col space-y-1">
                                         @if($request->student)
                                            <div class="flex items-center text-sm font-medium text-gray-900">
                                                <span class="text-gray-500 mr-1">Student:</span> {{ $request->student->ime }} {{ $request->student->prezime }}
                                            </div>
                                         @endif
                                        <div class="flex items-center text-sm text-gray-500">
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div class="mb-2 text-xs text-gray-500">
                                        <span class="font-medium">Profesor:</span>
                                        {{ $request->subjects->pluck('professor.name')->unique()->filter()->join(', ') ?: 'None assigned' }}
                                    </div>
                                    <ul class="list-disc pl-4 space-y-1">
                                        @foreach($request->subjects as $subject)
                                            <li>
                                                <span class="font-medium">{{ $subject->straniPredmet->naziv }}</span>
                                                <span class="text-xs text-gray-400">({{ $subject->professor->name ?? 'Unassigned' }})</span>
                                                @if($subject->fitPredmet)
                                                     <span class="text-green-600 font-bold">-> {{ $subject->fitPredmet->naziv }}</span>
                                                @elseif($subject->is_rejected)
                                                     <span class="text-red-500 font-bold">-> (Rejected)</span>
                                                @else
                                                     <span class="text-yellow-500 italic">-> (Na čekanju)</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        // Determine status color logic
                                        $color = match($request->status) {
                                            'accepted' => 'bg-green-100 text-green-800',
                                            'rejected' => 'bg-red-100 text-red-800',
                                            default => 'bg-yellow-100 text-yellow-800',
                                        };
                                        $statusText = match($request->status) {
                                            'accepted' => 'Prihvaćen',
                                            'rejected' => 'Odbijen',
                                            default => 'U obradi',
                                        };
                                        
                                        $totalSubjects = $request->subjects->count();
                                        $matchedSubjects = $request->subjects->whereNotNull('fit_predmet_id')->count();
                                        $rejectedSubjects = $request->subjects->where('is_rejected', true)->count();
                                        $processedSubjects = $matchedSubjects + $rejectedSubjects;
                                        
                                        $allProcessed = ($totalSubjects > 0 && $processedSubjects == $totalSubjects);
                                        $allRejected = ($totalSubjects > 0 && $rejectedSubjects == $totalSubjects);
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                        {{ $statusText }}
                                    </span>
                                    @if($request->status == 'pending')
                                        @if($allRejected)
                                            <div class="text-xs text-red-600 mt-1 font-bold">Profesor je odbio sve</div>
                                        @elseif($allProcessed)
                                            <div class="text-xs text-green-600 mt-1 font-bold">Spremno za reviziju</div>
                                        @else
                                            <div class="text-xs text-yellow-600 mt-1">Čekanje na profesora ({{ $processedSubjects }}/{{ $totalSubjects }})</div>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('prepis.mapping-request.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors">
                                            Pregledaj zahtjev
                                        </a>
                                        <form action="{{ route('prepis.mapping-request.destroy', $request->id) }}" method="POST" onsubmit="return confirm('Jeste li sigurni da želite da izbrišete ovaj zahtjev?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors">
                                                Izbriši
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-500 italic">
                                    Nema zahtjeva za prepis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>
</x-app-layout>
