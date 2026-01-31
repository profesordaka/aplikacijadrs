<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mapiraj predmete') }} - {{ $mappingRequest->fakultet->naziv }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('profesorDashboardShow') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                            &larr; Nazad na kontrolnu tablu
                        </a>
                    </div>

                    <div class="mb-6 select-none">
                        <h3 class="text-lg font-medium mb-4">Poveži strane predmete sa svojim predmetima</h3>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Foreign Subjects Column -->
                            <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                <h4 class="font-semibold text-gray-700 mb-2">Strani predmeti ({{ $mappingRequest->fakultet->naziv }})</h4>
                                <input type="text" id="search-foreign" placeholder="Pretraži predmet..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <div id="foreign-list" class="h-[500px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                    @foreach($mappingRequest->subjects as $reqSubject)
                                        @if(!$reqSubject->fit_predmet_id)
                                            @php
                                                $isMySubject = $reqSubject->professor_id == auth()->id();
                                            @endphp
                                            <div class="draggable-item bg-white p-2 rounded border border-gray-200 shadow-sm text-sm flex justify-between items-center group
                                                 {{ $isMySubject && !in_array($mappingRequest->status, ['accepted', 'rejected']) ? 'hover:border-indigo-400 transition-colors cursor-grab' : 'opacity-50 cursor-not-allowed bg-gray-100' }}"
                                                 @if($isMySubject && !in_array($mappingRequest->status, ['accepted', 'rejected']))
                                                     draggable="true"
                                                     data-id="{{ $reqSubject->id }}"
                                                     data-name="{{ $reqSubject->straniPredmet->naziv }}"
                                                     data-type="foreign"
                                                 @endif>
                                                <span class="flex flex-col flex-1 min-w-0">
                                                    <span class="truncate font-medium">{{ $reqSubject->straniPredmet->naziv }}</span>
                                                    <span class="text-[10px] text-gray-500">{{ $reqSubject->straniPredmet->ects }} ECTS</span>
                                                    @if(!$isMySubject)
                                                        <span class="text-[10px] text-gray-400 block">(Dodijeljeno: {{ $reqSubject->professor->name ?? 'Nepoznato' }})</span>
                                                    @endif
                                                </span>
                                                <a href="{{ route('nastavne-liste.index', $reqSubject->strani_predmet_id) }}" 
                                                   class="ml-2 text-gray-400 hover:text-blue-500 transition-colors" 
                                                   title="Nastavna lista"
                                                   onclick="event.stopPropagation()">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Drop Zone & Linked List -->
                            <div class="flex flex-col space-y-4">
                                @if(in_array($mappingRequest->status, ['accepted', 'rejected']))
                                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
                                        <p class="text-yellow-700 font-semibold">
                                            @php
                                                $statusPrev = match($mappingRequest->status) {
                                                    'accepted' => 'prihvaćen',
                                                    'rejected' => 'odbijen',
                                                    default => $mappingRequest->status
                                                };
                                            @endphp
                                            Ovaj zahtjev je {{ $statusPrev }}. Izmjene su zaključane.
                                        </p>
                                    </div>
                                @else
                                    <!-- Drop Zone -->
                                    <div id="drop-zone" class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-6 flex flex-col items-center justify-center transition-colors min-h-[150px]">
                                        <p class="text-blue-500 font-medium text-center mb-2">Prevucite strani predmet i vaš predmet ovdje za povezivanje</p>
                                        <div class="flex items-center space-x-4 w-full justify-center">
                                            <div id="drop-slot-foreign" class="w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2">
                                                Strani predmet
                                            </div>
                                            <span class="text-gray-400">+</span>
                                            <div id="drop-slot-local" class="w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2">
                                                Tvoj predmet
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Linked List -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm flex flex-col">
                                    <div class="p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg flex justify-between items-center">
                                        <h4 class="font-semibold text-gray-700">Povezani parovi</h4>
                                        @if(!in_array($mappingRequest->status, ['accepted', 'rejected']))
                                            <button id="save-btn" class="bg-green-600 hover:bg-green-700 text-white text-xs font-bold py-1 px-3 rounded shadow transition-colors">
                                                Sačuvaj povezivanja
                                            </button>
                                        @endif
                                    </div>
                                    <div id="linked-list" class="h-[350px] overflow-y-auto p-2 space-y-2">
                                        <!-- Pre-filled mappings if any -->
                                        @foreach($mappingRequest->subjects as $reqSubject)
                                            @if($reqSubject->fit_predmet_id)
                                                @php $isMySubject = $reqSubject->professor_id == auth()->id(); @endphp
                                                <div class="flex items-center justify-between p-3 bg-white border border-gray-200 rounded shadow-sm text-sm {{ !$isMySubject ? 'opacity-75 bg-gray-50' : '' }}" 
                                                     data-req-id="{{ $reqSubject->id }}" 
                                                     data-fit-id="{{ $reqSubject->fit_predmet_id }}"
                                                     data-foreign-name="{{ $reqSubject->straniPredmet->naziv }}"
                                                     data-local-name="{{ $reqSubject->fitPredmet->naziv }}">
                                                    <div class="flex-1 flex items-center gap-2 min-w-0">
                                                        <div class="flex-1 truncate font-medium text-gray-800 flex items-center gap-1" title="{{ $reqSubject->straniPredmet->naziv }}">
                                                            <a href="{{ route('nastavne-liste.index', $reqSubject->strani_predmet_id) }}" class="text-gray-400 hover:text-blue-500">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                            </a>
                                                            <span class="truncate">{{ $reqSubject->straniPredmet->naziv }}</span>
                                                            @if(!$isMySubject) <span class="text-[10px] text-gray-400 block whitespace-nowrap">({{ $reqSubject->professor->name ?? '?' }})</span> @endif
                                                        </div>
                                                        <div class="flex-shrink-0 text-gray-400">
                                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                                            </svg>
                                                        </div>
                                                        <div class="flex-1 truncate text-gray-600 text-right flex items-center justify-end gap-1" title="{{ $reqSubject->fitPredmet->naziv }}">
                                                            <span class="truncate">{{ $reqSubject->fitPredmet->naziv }}</span>
                                                            <a href="{{ route('nastavne-liste.index', $reqSubject->fit_predmet_id) }}" class="text-gray-400 hover:text-blue-500">
                                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @if($isMySubject && !in_array($mappingRequest->status, ['accepted', 'rejected']))
                                                        <button type="button" class="ml-3 text-red-500 hover:text-red-700 font-bold px-2" onclick="unlinkPair(this, '{{ $reqSubject->id }}', '{{ $reqSubject->straniPredmet->naziv }}', '{{ $reqSubject->straniPredmet->ects }}', '{{ $reqSubject->strani_predmet_id }}')">&times;</button>
                                                    @endif
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- Professor Subjects Column -->
                            <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                <h4 class="font-semibold text-gray-700 mb-2">Tvoji predmeti</h4>
                                <input type="text" id="search-local" placeholder="Pretraži predmet..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <div id="local-list" class="h-[500px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                    @foreach($professorSubjects as $subject)
                                        <div class="draggable-item bg-white p-2 rounded border border-gray-200 shadow-sm text-sm {{ !in_array($mappingRequest->status, ['accepted', 'rejected']) ? 'hover:border-indigo-400 transition-colors cursor-grab' : 'opacity-50 cursor-not-allowed bg-gray-100' }} flex justify-between items-center group"
                                             @if(!in_array($mappingRequest->status, ['accepted', 'rejected']))
                                                 draggable="true"
                                             @endif
                                             data-id="{{ $subject->id }}"
                                             data-name="{{ $subject->naziv }}"
                                             data-type="local">
                                            <span class="flex-1 min-w-0">
                                                <span class="truncate block font-medium">{{ $subject->naziv }}</span>
                                                <span class="text-[10px] text-gray-500">{{ $subject->ects }} ECTS</span>
                                            </span>
                                            <a href="{{ route('nastavne-liste.index', $subject->id) }}" 
                                               class="ml-2 text-gray-400 hover:text-blue-500 transition-colors" 
                                               title="Nastavna lista"
                                               onclick="event.stopPropagation()">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                                </svg>
                                            </a>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .draggable-item {
            cursor: grab;
            user-select: none;
            position: relative;
        }
        .draggable-item:active {
            cursor: grabbing;
            z-index: 10;
        }
        .draggable-item.dragging {
            opacity: 0.5;
            transform: scale(0.95);
        }
        .drop-active {
            background-color: #e0e7ff; /* indigo-100 */
            border-color: #6366f1; /* indigo-500 */
        }
    </style>

            <!-- Confirmation Modal -->
            <div id="confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
                <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full p-6 animate-fade-in-down max-h-[90vh] flex flex-col">
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Potvrdi povezivanja</h3>
                    <p class="text-gray-600 mb-4">Pregledajte izmjene prije čuvanja.</p>
                    
                    <div class="flex-1 overflow-y-auto space-y-4 mb-6 pr-2">
                        <!-- Accepted Section -->
                        <div>
                            <h4 class="text-sm font-bold text-green-700 uppercase tracking-wide border-b border-green-200 pb-1 mb-2">
                                Prihvatam (<span id="count-accepted">0</span>)
                            </h4>
                            <ul id="list-accepted" class="space-y-1">
                                <!-- JS Injected -->
                            </ul>
                            <p id="none-accepted" class="text-gray-400 text-sm italic hidden">Nema mapiranih predmeta.</p>
                        </div>

                        <!-- Rejected Section -->
                        <div>
                            <h4 class="text-sm font-bold text-red-700 uppercase tracking-wide border-b border-red-200 pb-1 mb-2">
                                Odbijam (<span id="count-rejected">0</span>)
                            </h4>
                            <p class="text-xs text-red-500 mb-2">Ovi predmeti će biti označeni kao "Odbijeni".</p>
                            <ul id="list-rejected" class="space-y-1">
                                <!-- JS Injected -->
                            </ul>
                            <p id="none-rejected" class="text-gray-400 text-sm italic hidden">Nema odbijenih predmeta.</p>
                        </div>
                        
                        <!-- Comment Section -->
                        <div class="mt-4">
                            <label for="professor-comment" class="block text-sm font-medium text-gray-700 mb-1">Napomena / Komentar (opciono)</label>
                            <textarea id="professor-comment" rows="3" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Ovdje možete ostaviti komentar za admina..."></textarea>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t border-gray-100">
                        <button id="cancel-modal-btn" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold px-4 py-2 rounded transition-colors">
                            Otkaži
                        </button>
                        <button id="confirm-modal-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-4 py-2 rounded shadow transition-colors">
                            Potvrdi i sačuvaj
                        </button>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    // Total assigned subjects for this professor (injected from Backend)
                    const totalAssignedSubjects = {{ $mappingRequest->subjects->where('professor_id', auth()->id())->count() }};

                    // State
                    let state = {
                        pendingForeign: null,
                        pendingLocal: null,
                        mappings: [] // { request_subject_id, fit_predmet_id, foreign_name, local_name }
                    };

                    // DOM Elements
                    const els = {
                        foreignList: document.getElementById('foreign-list'),
                        localList: document.getElementById('local-list'),
                        linkedList: document.getElementById('linked-list'),
                        dropZone: document.getElementById('drop-zone'),
                        dropSlotForeign: document.getElementById('drop-slot-foreign'),
                        dropSlotLocal: document.getElementById('drop-slot-local'),
                        searchLocal: document.getElementById('search-local'),
                        searchForeign: document.getElementById('search-foreign'),
                        saveBtn: document.getElementById('save-btn'),
                        // Modal
                        confirmModal: document.getElementById('confirm-modal'),
                        confirmModalBtn: document.getElementById('confirm-modal-btn'),
                        cancelModalBtn: document.getElementById('cancel-modal-btn'),
                        // Modal Lists
                        listAccepted: document.getElementById('list-accepted'),
                        listRejected: document.getElementById('list-rejected'),
                        countAccepted: document.getElementById('count-accepted'),
                        countRejected: document.getElementById('count-rejected'),
                        noneAccepted: document.getElementById('none-accepted'),
                        noneRejected: document.getElementById('none-rejected'),
                    };

                    // Initialize existing mappings from DOM
                    function initMappings() {
                        const existing = els.linkedList.querySelectorAll('div[data-req-id]');
                        existing.forEach(el => {
                            state.mappings.push({
                                request_subject_id: el.dataset.reqId,
                                fit_predmet_id: el.dataset.fitId,
                                foreign_name: el.dataset.foreignName,
                                local_name: el.dataset.localName
                            });
                        });
                    }

                    // --- Drag and Drop Logic --- (Identical to before)
                    function setupDragAndDrop() {
                        const draggables = document.querySelectorAll('.draggable-item');
                        draggables.forEach(item => {
                            item.addEventListener('dragstart', (e) => {
                                item.classList.add('dragging');
                                e.dataTransfer.setData('text/plain', JSON.stringify({
                                    id: item.dataset.id,
                                    type: item.dataset.type,
                                    name: item.dataset.name
                                }));
                                e.dataTransfer.effectAllowed = 'move';
                            });
                            item.addEventListener('dragend', () => {
                                item.classList.remove('dragging');
                            });
                        });

                        const zone = els.dropZone;
                        zone.addEventListener('dragover', (e) => {
                            e.preventDefault();
                            zone.classList.add('drop-active');
                        });
                        zone.addEventListener('dragleave', () => {
                            zone.classList.remove('drop-active');
                        });
                        zone.addEventListener('drop', (e) => {
                            e.preventDefault();
                            zone.classList.remove('drop-active');
                            const data = e.dataTransfer.getData('text/plain');
                            if (!data) return;
                            try {
                                const itemData = JSON.parse(data);
                                if (itemData.type === 'foreign') {
                                    state.pendingForeign = itemData;
                                } else {
                                    state.pendingLocal = itemData;
                                }
                                renderDropZone();
                                if (state.pendingForeign && state.pendingLocal) {
                                    addMapping(state.pendingForeign, state.pendingLocal);
                                    state.pendingForeign = null;
                                    state.pendingLocal = null;
                                    renderDropZone();
                                }
                            } catch (err) {
                                console.error('Drop error', err);
                            }
                        });
                    }

                    function renderDropZone() {
                        if (state.pendingForeign) {
                            els.dropSlotForeign.textContent = state.pendingForeign.name;
                            els.dropSlotForeign.className = "w-1/2 h-12 bg-indigo-50 border border-indigo-300 text-indigo-700 rounded flex items-center justify-center text-xs text-center px-2 font-medium relative group cursor-pointer";
                            els.dropSlotForeign.innerHTML += `<span class="hidden group-hover:flex absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 items-center justify-center text-[10px]" onclick="clearSlot('foreign', event)">x</span>`;
                        } else {
                            els.dropSlotForeign.textContent = "Strani predmet";
                            els.dropSlotForeign.className = "w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2";
                        }

                        if (state.pendingLocal) {
                            els.dropSlotLocal.textContent = state.pendingLocal.name;
                            els.dropSlotLocal.className = "w-1/2 h-12 bg-indigo-50 border border-indigo-300 text-indigo-700 rounded flex items-center justify-center text-xs text-center px-2 font-medium relative group cursor-pointer";
                            els.dropSlotLocal.innerHTML += `<span class="hidden group-hover:flex absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 items-center justify-center text-[10px]" onclick="clearSlot('local', event)">x</span>`;
                        } else {
                            els.dropSlotLocal.textContent = "Tvoj predmet";
                            els.dropSlotLocal.className = "w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2";
                        }
                    }

                    function addMapping(foreign, local) {
                        if (state.mappings.some(m => m.request_subject_id == foreign.id)) {
                            alert('Ovaj strani predmet je već mapiran.');
                            return;
                        }
                        state.mappings.push({
                            request_subject_id: foreign.id,
                            fit_predmet_id: local.id,
                            foreign_name: foreign.name,
                            local_name: local.name
                        });
                        const foreignEl = els.foreignList.querySelector(`[data-id="${foreign.id}"]`);
                        if (foreignEl) foreignEl.remove();
                        renderLinkedList();
                    }

                    function renderLinkedList() {
                        els.linkedList.innerHTML = '';
                        state.mappings.forEach(m => {
                            const el = document.createElement('div');
                            el.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded shadow-sm text-sm';
                            el.dataset.reqId = m.request_subject_id;
                            el.dataset.fitId = m.fit_predmet_id;
                            el.innerHTML = `
                                <div class="flex-1 flex items-center gap-2 min-w-0">
                                    <div class="flex-1 truncate font-medium text-gray-800" title="${m.foreign_name}">${m.foreign_name}</div>
                                    <div class="flex-shrink-0 text-gray-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                                        </svg>
                                    </div>
                                    <div class="flex-1 truncate text-gray-600 text-right" title="${m.local_name}">${m.local_name}</div>
                                </div>
                                <button type="button" class="ml-3 text-red-500 hover:text-red-700 font-bold px-2" onclick="unlinkPair(this, '${m.request_subject_id}', '${m.foreign_name}', '0')">&times;</button>
                            `;
                            els.linkedList.appendChild(el);
                        });
                    }

                    window.unlinkPair = function(btn, reqId, name, ects, straniPredmetId) {
                        const idx = state.mappings.findIndex(m => m.request_subject_id == reqId);
                        if (idx > -1) {
                            state.mappings.splice(idx, 1);
                        }
                        const div = document.createElement('div');
                        div.className = 'draggable-item bg-white p-2 rounded border border-gray-200 shadow-sm text-sm hover:border-indigo-400 transition-colors flex justify-between items-center group cursor-grab';
                        div.draggable = true;
                        div.dataset.id = reqId;
                        div.dataset.name = name;
                        div.dataset.type = 'foreign';
                        
                        // Recreate the syllabus link
                        const syllabusUrl = `{{ route('nastavne-liste.index', ':id') }}`.replace(':id', straniPredmetId);
                        
                        div.innerHTML = `
                            <span class="flex flex-col flex-1 min-w-0">
                                <span class="truncate font-medium">${name}</span>
                                <span class="text-[10px] text-gray-500">${ects} ECTS</span>
                            </span>
                            <a href="${syllabusUrl}" 
                               class="ml-2 text-gray-400 hover:text-blue-500 transition-colors" 
                               title="Nastavna lista"
                               onclick="event.stopPropagation()">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </a>
                        `;
                        
                        div.addEventListener('dragstart', (e) => {
                            div.classList.add('dragging');
                            e.dataTransfer.setData('text/plain', JSON.stringify({
                                id: reqId,
                                 type: 'foreign',
                                name: name
                            }));
                            e.dataTransfer.effectAllowed = 'move';
                        });
                        div.addEventListener('dragend', () => {
                            div.classList.remove('dragging');
                        });
                        els.foreignList.appendChild(div);
                        renderLinkedList();
                    };

                    window.clearSlot = function(type, e) {
                        e.stopPropagation();
                        if (type === 'foreign') state.pendingForeign = null;
                        if (type === 'local') state.pendingLocal = null;
                        renderDropZone();
                    };

                    els.searchLocal.addEventListener('input', () => {
                        const query = els.searchLocal.value.toLowerCase();
                        const items = els.localList.querySelectorAll('.draggable-item');
                        items.forEach(item => {
                            if (item.dataset.name.toLowerCase().includes(query)) {
                                item.classList.remove('hidden');
                            } else {
                                item.classList.add('hidden');
                            }
                        });
                    });

                    els.searchForeign.addEventListener('input', () => {
                        const query = els.searchForeign.value.toLowerCase();
                        const items = els.foreignList.querySelectorAll('.draggable-item');
                        items.forEach(item => {
                            if (item.dataset.name.toLowerCase().includes(query)) {
                                item.classList.remove('hidden');
                            } else {
                                item.classList.add('hidden');
                            }
                        });
                    });

                    // --- Confirmation & Save Logic ---
                    els.saveBtn.addEventListener('click', () => {
                        // 1. Accepted Mappings
                        const accepted = state.mappings;
                        els.listAccepted.innerHTML = '';
                        els.countAccepted.textContent = accepted.length;
                        
                        if (accepted.length > 0) {
                            els.noneAccepted.classList.add('hidden');
                            accepted.forEach(m => {
                                const li = document.createElement('li');
                                li.className = 'text-sm flex justify-between items-center p-2 bg-green-50 rounded border border-green-100';
                                li.innerHTML = `
                                    <span class="font-medium text-gray-800">${m.foreign_name}</span>
                                    <span class="text-gray-400 text-xs mx-1">-></span>
                                    <span class="text-green-700 font-semibold">${m.local_name}</span>
                                `;
                                els.listAccepted.appendChild(li);
                            });
                        } else {
                            els.noneAccepted.classList.remove('hidden');
                        }

                        // 2. Rejected Subjects (Everything currently in #foreign-list that is draggable)
                        // Note: foreignList contains logic to only make 'isMySubject' draggable.
                        // We select those.
                        const rejectedNodes = els.foreignList.querySelectorAll('.draggable-item[draggable="true"]');
                        const rejected = Array.from(rejectedNodes).map(node => ({
                            name: node.dataset.name
                        }));

                        els.listRejected.innerHTML = '';
                        els.countRejected.textContent = rejected.length;
                        
                        if (rejected.length > 0) {
                            els.noneRejected.classList.add('hidden');
                            rejected.forEach(r => {
                                const li = document.createElement('li');
                                li.className = 'text-sm text-red-700 font-medium p-2 bg-red-50 rounded border border-red-100';
                                li.textContent = r.name;
                                els.listRejected.appendChild(li);
                            });
                        } else {
                            els.noneRejected.classList.remove('hidden');
                        }
                        
                        els.confirmModal.classList.remove('hidden');
                    });

                    els.cancelModalBtn.addEventListener('click', () => {
                        els.confirmModal.classList.add('hidden');
                    });

                    els.confirmModalBtn.addEventListener('click', async () => {
                        els.confirmModal.classList.add('hidden'); // Hide immediately
                        
                        const mappings = state.mappings.map(m => ({
                            request_subject_id: m.request_subject_id,
                            fit_predmet_id: m.fit_predmet_id
                        }));

                        try {
                            const response = await fetch('{{ route("mapping-request.update", $mappingRequest->id) }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                },
                                body: JSON.stringify({
                                    mappings: mappings,
                                    comment: document.getElementById('professor-comment').value
                                })
                            });

                            if (response.ok) {
                                window.location.href = '{{ route("profesorDashboardShow") }}';
                            } else {
                                alert('Greška pri čuvanju mapiranja. Pokušajte ponovo.');
                            }
                        } catch (error) {
                            console.error('Error:', error);
                            alert('Došlo je do greške.');
                        }
                    });

                    initMappings();
                    setupDragAndDrop();
                });
            </script>
</x-app-layout>
