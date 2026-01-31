<x-app-layout>
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-800 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 bg-red-100 text-red-800 p-3 rounded-md">
            {{ session('error') }}
        </div>
    @endif

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900">Pregled zahtjeva za prepis</h1>
                <a href="{{ route('prepis.index') }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; Nazad na kontrolnu tablu
                </a>
            </div>

            <!-- Request Details -->
            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Informacije o zahtjevu</h2>
                    @php
                        $color = match($mappingRequest->status) {
                            'accepted' => 'bg-green-100 text-green-800',
                            'rejected' => 'bg-red-100 text-red-800',
                            default => 'bg-yellow-100 text-yellow-800',
                        };
                        $statusText = ucfirst($mappingRequest->status); 
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-bold {{ $color }}">
                        {{ $statusText }}
                    </span>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Student</h3>
                        @if($mappingRequest->student)
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                    {{ substr($mappingRequest->student->ime, 0, 1) }}{{ substr($mappingRequest->student->prezime, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-lg font-medium text-gray-900">{{ $mappingRequest->student->ime }} {{ $mappingRequest->student->prezime }}</div>
                                    <div class="text-sm text-gray-500">{{ $mappingRequest->student->br_indexa }}</div>
                                </div>
                            </div>
                        @else
                            <span class="text-gray-500 italic">Nema povezanog studenta</span>
                        @endif
                    </div>
                    <div>
                        <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-2">Profesor</h3>
                        @php
                            $uniqueProfessors = $mappingRequest->subjects->pluck('professor')->unique('id')->filter();
                        @endphp

                        @if($uniqueProfessors->count() == 1)
                            @php $prof = $uniqueProfessors->first(); @endphp
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-green-100 flex items-center justify-center text-green-600 font-bold">
                                    {{ substr($prof->name, 0, 1) }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-lg font-medium text-gray-900">{{ $prof->name }}</div>
                                </div>
                            </div>
                        @elseif($uniqueProfessors->count() > 1)
                            <div class="flex items-start">
                                <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center text-blue-600 font-bold">
                                    M
                                </div>
                                <div class="ml-4">
                                    <div class="text-lg font-medium text-gray-900">Više profesora</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $uniqueProfessors->pluck('name')->join(', ') }}
                                    </div>
                                </div>
                            </div>
                         @else
                            <span class="text-gray-500 italic">Nema dodijeljenih profesora</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Professor Comments -->
            @if($mappingRequest->comments->isNotEmpty())
                <div class="mt-6 bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Napomene profesora</h3>
                    <div class="space-y-4">
                        @foreach($mappingRequest->comments as $comment)
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-100">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-medium text-gray-900">{{ $comment->professor->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</div>
                                    </div>
                                <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ $comment->comment }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Subject Mappings -->
            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Povezani predmeti</h2>
                    <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $mappingRequest->subjects->count() }} Predmeta</span>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strani predmet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dodijeljeni profesor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Povezani FIT predmet</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Radnje</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($mappingRequest->subjects as $subject)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $subject->straniPredmet->naziv }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $subject->professor->name ?? 'Nedodijeljeno' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        @php
                                            // Status logic:
                                            // Yellow: Pending (fit_predmet_id is null, request is pending)
                                            // Green: Matched (fit_predmet_id is not null)
                                            // Red: Unmatched/Rejected (fit_predmet_id is null, request is accepted/rejected)
                                            
                                            $subjectStatusColor = 'text-gray-900';
                                            $subjectStatusBg = '';
                                            
                                            if ($subject->fit_predmet_id) {
                                                // Matched -> Green
                                                $subjectStatusColor = 'text-green-700 font-semibold';
                                                $subjectStatusBg = 'bg-green-50';
                                            } elseif ($subject->is_rejected) {
                                                // Explicitly rejected by professor -> Red
                                                $subjectStatusColor = 'text-red-700 font-semibold';
                                                $subjectStatusBg = 'bg-red-50';
                                            } elseif ($mappingRequest->status == 'pending') {
                                                // Not matched yet, but pending and NOT rejected -> Yellow
                                                $subjectStatusColor = 'text-yellow-700 italic';
                                                $subjectStatusBg = 'bg-yellow-50';
                                            } else {
                                                // Fallback for weird states
                                                $subjectStatusColor = 'text-gray-700';
                                                $subjectStatusBg = 'bg-gray-100';
                                            }
                                        @endphp

                                        <div class="p-2 rounded-md {{ $subjectStatusBg }} {{ $subjectStatusColor }}">
                                            @if($subject->fitPredmet)
                                                {{ $subject->fitPredmet->naziv }}
                                            @elseif($subject->is_rejected)
                                                <span>Odbijeno od strane profesora</span>
                                            @else
                                                <span>Nema par / Na čekanju</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        @if(in_array($mappingRequest->status, ['pending', 'completed']))
                                            <form action="{{ route('prepis.mapping-request.subject.remove', $subject->id) }}" method="POST" onsubmit="return confirm('Ukloni ovaj predmet iz zahtjeva?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 hover:text-red-700 transition-colors" title="Ukloni predmet">
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                                    </svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-gray-400 text-xs">Radnje zaključane</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            @if($mappingRequest->subjects->isEmpty())
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500 italic">
                                        Nema predmeta u ovom zahtjevu.
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Add New Subject Form -->
            @if(in_array($mappingRequest->status, ['pending', 'completed']))

            <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200 mt-6 select-none">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-800">Poveži predmete studenta sa profesorom</h2>
                    <span class="text-xs text-gray-500">Prevucite za povezivanje</span>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Student Subjects Column (Source) -->
                        <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                            <h4 class="font-semibold text-gray-700 mb-2">Nemapirani predmeti studenta</h4>
                            <input type="text" id="search-subject" placeholder="Pretraži predmet..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <div id="subject-list" class="h-[400px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                <!-- Injected via JS -->
                            </div>
                        </div>

                        <!-- Drop Zone & Linked List -->
                        <div class="flex flex-col space-y-4">
                            <!-- Drop Zone -->
                            <div id="drop-zone" class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-4 flex flex-col items-center justify-center transition-colors min-h-[120px]">
                                <p class="text-blue-500 font-medium text-center mb-2 text-sm">Prevucite predmet i profesora ovdje</p>
                                <div class="flex items-center space-x-2 w-full justify-center">
                                    <div id="drop-slot-subject" class="w-1/2 h-10 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-1 truncate">Predmet</div>
                                    <span class="text-gray-400 text-xs">+</span>
                                    <div id="drop-slot-prof" class="w-1/2 h-10 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-1 truncate">Profesor</div>
                                </div>
                            </div>

                            <!-- Linked List -->
                            <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm flex flex-col h-[260px]">
                                <div class="p-2 border-b border-gray-200 bg-gray-50 rounded-t-lg flex justify-between items-center">
                                    <h4 class="font-semibold text-gray-700 text-sm">Nova povezivanja</h4>
                                    <button id="send-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-1 px-3 rounded shadow transition-colors hidden">
                                        Dodaj u zahtjev
                                    </button>
                                </div>
                                <div id="linked-list" class="overflow-y-auto p-2 space-y-2 flex-1">
                                    <!-- Linked Pairs -->
                                </div>
                            </div>
                        </div>

                        <!-- Professors Column -->
                         <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                            <h4 class="font-semibold text-gray-700 mb-2">Profesori</h4>
                            <input type="text" id="search-prof" placeholder="Pretraži profesora..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <div id="prof-list" class="h-[400px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                <!-- Injected via JS -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <style>
                .draggable-item { cursor: grab; user-select: none; }
                .draggable-item:active { cursor: grabbing; z-index: 10; }
                .draggable-item.dragging { opacity: 0.5; transform: scale(0.95); }
                .drop-active { background-color: #e0e7ff; border-color: #6366f1; }
            </style>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    const allProfessors = @json($professors);
                    const studentSubjects = @json($studentSubjects);
                    
                    let state = {
                        professors: allProfessors,
                        subjects: studentSubjects,
                        linkedPairs: [],
                        pendingProf: null,
                        pendingSubject: null,
                    };

                    const els = {
                        profList: document.getElementById('prof-list'),
                        subjectList: document.getElementById('subject-list'),
                        linkedList: document.getElementById('linked-list'),
                        dropZone: document.getElementById('drop-zone'),
                        dropSlotProf: document.getElementById('drop-slot-prof'),
                        dropSlotSubject: document.getElementById('drop-slot-subject'),
                        searchProf: document.getElementById('search-prof'),
                        searchSubject: document.getElementById('search-subject'),
                        sendBtn: document.getElementById('send-btn'),
                    };

                    function init() {
                        render();
                        setupDragAndDrop();
                        setupSearch();
                        setupSendButton();
                    }

                    function render() {
                        renderList('professor', state.professors, els.profList, els.searchProf.value);
                        renderList('subject', state.subjects, els.subjectList, els.searchSubject.value);
                        renderLinkedList();
                        renderDropZone();
                        updateSendButton();
                    }

                    function renderList(type, data, container, query) {
                        container.innerHTML = '';
                        const q = query.toLowerCase();
                        data.filter(item => (item.name || item.naziv).toLowerCase().includes(q))
                            .forEach(item => {
                                container.appendChild(createDraggableItem(item, type));
                            });
                    }

                    function createDraggableItem(item, type) {
                        const div = document.createElement('div');
                        div.className = 'draggable-item bg-white p-2 rounded border border-gray-200 shadow-sm text-sm hover:border-indigo-400 transition-colors mb-2 cursor-grab';
                        div.draggable = true;
                        div.textContent = type === 'professor' ? item.name : `${item.naziv} (${item.ects} ECTS)`;
                        
                        div.addEventListener('dragstart', (e) => {
                            div.classList.add('dragging');
                            e.dataTransfer.setData('text/plain', JSON.stringify({ id: item.id, type: type }));
                        });
                        div.addEventListener('dragend', () => div.classList.remove('dragging'));
                        return div;
                    }

                    function renderLinkedList() {
                        els.linkedList.innerHTML = '';
                        state.linkedPairs.forEach((pair, index) => {
                            const el = document.createElement('div');
                            el.className = 'flex items-center justify-between p-2 bg-indigo-50 border border-indigo-100 rounded text-xs';
                            el.innerHTML = `
                                <div class="truncate flex-1">
                                    <div class="font-bold text-gray-700 truncate">${pair.subject.naziv}</div>
                                    <div class="text-gray-500 truncate">-> ${pair.prof.name}</div>
                                </div>
                                <button class="text-red-500 font-bold ml-2" onclick="unlinkPair(${index})">&times;</button>
                            `;
                            els.linkedList.appendChild(el);
                        });
                    }

                    function renderDropZone() {
                         updateSlot(els.dropSlotProf, state.pendingProf, 'professor', 'Professor');
                         updateSlot(els.dropSlotSubject, state.pendingSubject, 'subject', 'Subject');
                    }

                    function updateSlot(el, item, type, placeholder) {
                        if (item) {
                            el.textContent = item.name || item.naziv;
                            el.className = "w-1/2 h-10 bg-indigo-100 border border-indigo-300 text-indigo-800 rounded flex items-center justify-center text-xs text-center px-1 font-bold relative group cursor-pointer";
                            el.onclick = () => { state[type === 'professor' ? 'pendingProf' : 'pendingSubject'] = null; render(); };
                        } else {
                            if (type === 'professor') {
                                el.textContent = 'Profesor';
                            } else {
                                el.textContent = 'Predmet';
                            }
                            el.className = "w-1/2 h-10 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-1 truncate";
                            el.onclick = null;
                        }
                    }

                    window.unlinkPair = (index) => {
                        state.linkedPairs.splice(index, 1);
                        render();
                    };

                    function setupDragAndDrop() {
                        const zone = els.dropZone;
                        zone.addEventListener('dragover', (e) => { e.preventDefault(); zone.classList.add('drop-active'); });
                        zone.addEventListener('dragleave', () => zone.classList.remove('drop-active'));
                        zone.addEventListener('drop', (e) => {
                            e.preventDefault();
                            zone.classList.remove('drop-active');
                            const data = JSON.parse(e.dataTransfer.getData('text/plain'));
                            
                            if (data.type === 'professor') state.pendingProf = state.professors.find(p => p.id == data.id);
                            if (data.type === 'subject') state.pendingSubject = state.subjects.find(s => s.id == data.id);

                            if (state.pendingProf && state.pendingSubject) {
                                if (!state.linkedPairs.some(p => p.subject.id == state.pendingSubject.id)) {
                                    state.linkedPairs.push({ prof: state.pendingProf, subject: state.pendingSubject });
                                    // Remove used subject from list logic? 
                                    // Typically yes, to prevent double assign.
                                    // Filter out from state.subjects? 
                                    // state.subjects = state.subjects.filter(s => s.id !== state.pendingSubject.id);
                                    state.pendingProf = null;
                                    state.pendingSubject = null;
                                } else {
                                    alert('Predmet je već dodat na listu za povezivanje.');
                                }
                            }
                            render();
                        });
                    }

                    function setupSearch() {
                        els.searchProf.addEventListener('input', () => renderList('professor', state.professors, els.profList, els.searchProf.value));
                        els.searchSubject.addEventListener('input', () => renderList('subject', state.subjects, els.subjectList, els.searchSubject.value));
                    }

                    function updateSendButton() {
                         els.sendBtn.classList.toggle('hidden', state.linkedPairs.length === 0);
                    }

                    function setupSendButton() {
                        els.sendBtn.addEventListener('click', async () => {
                            if (!confirm('Dodaj ove predmete u zahtjev?')) return;
                            
                            const matches = state.linkedPairs.map(p => ({
                                professor_id: p.prof.id,
                                subject_id: p.subject.id
                            }));

                            try {
                                const response = await fetch('{{ route("prepis.mapping-request.subject.bulk-add", $mappingRequest->id) }}', {
                                    method: 'POST',
                                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                                    body: JSON.stringify({ matches })
                                });
                                
                                const data = await response.json().catch(() => ({}));
                                
                                if (response.ok) {
                                    window.location.reload();
                                } else {
                                    alert('Greška pri dodavanju predmeta: ' + (data.message || response.statusText));
                                    console.error('Server Error:', data);
                                }
                            } catch (e) {
                                console.error(e);
                                alert('Došlo je do greške: ' + e.message);
                            }
                        });
                    }

                    init();
                });
            </script>
            @endif

            <!-- Global Actions -->
            <div class="flex justify-end space-x-4 pt-4 border-t border-gray-200">
                @if($mappingRequest->status === 'accepted')
                     <form action="{{ route('prepis.mapping-request.export-word', $mappingRequest->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-6 py-2 rounded-lg shadow-lg transform transition hover:scale-105 flex items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                            </svg>
                            Eksportuj prepis
                        </button>
                    </form>
                @endif
                
                @if(in_array($mappingRequest->status, ['pending', 'completed']))
                    @php
                        $isEmpty = $mappingRequest->subjects->isEmpty();
                        $hasUnresolvedSubjects = $mappingRequest->subjects->contains(fn($s) => is_null($s->fit_predmet_id));
                        $isActionsDisabled = $isEmpty || $hasUnresolvedSubjects;
                    @endphp

                    @if($isActionsDisabled)
                        <div class="flex items-center text-yellow-600 mr-4 text-sm font-medium">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            @if($isEmpty)
                                Dodajte predmete u zahtjev da biste omogućili radnje.
                            @else
                                Riješite sve predmete (na čekanju ili odbijene) da biste omogućili radnje.
                            @endif
                        </div>
                    @endif

                    <form action="{{ route('prepis.mapping-request.reject', $mappingRequest->id) }}" method="POST" onsubmit="return confirm('Jeste li sigurni da želite da odbijete cijeli zahtjev?');">
                        @csrf
                        <button type="submit" 
                            class="bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 font-semibold px-4 py-2 rounded-lg shadow-sm transition-colors disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-red-50"
                            @if($isActionsDisabled) disabled @endif
                        >
                            Odbij zahtjev
                        </button>
                    </form>
                    
                    <form action="{{ route('prepis.mapping-request.accept', $mappingRequest->id) }}" method="POST" onsubmit="return confirm('Jeste li sigurni da želite da prihvatite ovaj zahtjev i kreirate prepis?');">
                        @csrf
                        <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white font-semibold px-6 py-2 rounded-lg shadow-lg transform transition hover:scale-105 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
                            @if($isActionsDisabled) disabled @endif
                        >
                            Prihvati zahtjev
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
