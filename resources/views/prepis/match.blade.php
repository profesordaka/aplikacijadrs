<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kreiranje zahtjeva za prepis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('prepis.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                            &larr; Nazad na upravljanje prepisima
                        </a>
                    </div>

                    <!-- Student Selection -->
                    <div class="mb-6">
                        <label for="student_id" class="block text-sm font-medium text-gray-700">Izaberi studenta</label>
                        <div class="relative searchable-container" data-type="student">
                            <input type="text" class="search-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Pretraži studenta..." autocomplete="off">
                            <div class="search-results absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden"></div>
                            <select name="student_id" id="student_id" class="hidden">
                                <option value="">Izaberi studenta</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}" data-text="{{ $student->ime }} {{ $student->prezime }} ({{ $student->br_indexa }})">{{ $student->ime }} {{ $student->prezime }} ({{ $student->br_indexa }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Drag and Drop Interface -->
                    <div class="mb-6 select-none">
                        <h3 class="text-lg font-medium mb-4">Poveži predmet studenta sa profesorom</h3>
                        
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Student Subjects Column (Source) -->
                            <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                <h4 class="font-semibold text-gray-700 mb-2">Predmeti studenta</h4>
                                <input type="text" id="search-subject" placeholder="Pretraži predmet..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" disabled>
                                <div id="subject-list" class="h-[500px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                    <p class="text-gray-500 text-sm text-center mt-4">Izaberi studenta/fakultet da vidiš predmete</p>
                                </div>
                            </div>

                            <!-- Drop Zone & Linked List -->
                            <div class="flex flex-col space-y-4">
                                <!-- Drop Zone -->
                                <div id="drop-zone" class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-6 flex flex-col items-center justify-center transition-colors min-h-[150px]">
                                    <p class="text-blue-500 font-medium text-center mb-2">Prevucite predmet i profesora ovdje da biste ih povezali</p>
                                    <div class="flex items-center space-x-4 w-full justify-center">
                                        
                                        <div id="drop-slot-subject" class="w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2">
                                            Predmeti
                                        </div>
                                        <span class="text-gray-400">+</span>
                                        <div id="drop-slot-prof" class="w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2">
                                            Profesor
                                        </div>
                                    </div>
                                </div>

                                <!-- Linked List -->
                                <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm flex flex-col">
                                    <div class="p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg flex justify-between items-center">
                                        <h4 class="font-semibold text-gray-700">Povezani parovi</h4>
                                        <button id="send-btn" class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold py-1 px-3 rounded shadow transition-colors hidden">
                                            Sačuvaj
                                        </button>
                                    </div>
                                    <div id="linked-list" class="h-[350px] overflow-y-auto p-2 space-y-2">
                                        <!-- Linked Pairs will be injected here -->
                                    </div>
                                </div>
                            </div>

                            <!-- Professors Column -->
                             <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                <h4 class="font-semibold text-gray-700 mb-2">Profesori</h4>
                                <input type="text" id="search-prof" placeholder="Pretraži profesore..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <div id="prof-list" class="h-[500px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                    <!-- Professor Items will be injected here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Match Info Modal -->
    <div id="matchInfoModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-[60]">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md overflow-hidden transform transition-all">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Informacije o paru</h3>
                <button type="button" onclick="closeMatchModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <div id="matchInfoContent" class="space-y-4">
                    <!-- Content will be injected here -->
                </div>
            </div>
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end">
                <button type="button" onclick="closeMatchModal()" class="px-4 py-2 bg-white border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors">
                    Zatvori
                </button>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allProfessors = @json($professors);
            const previousMatches = @json($previousMatches);
            const globalPendingMatches = @json($globalPendingMatches);
            
            // State
            let state = {
                professors: [],
                subjects: [],
                linkedPairs: [],
                pendingProf: null,
                pendingSubject: null,
                selectedStudentId: null
            };

            // DOM Elements
            const els = {
                profList: document.getElementById('prof-list'),
                subjectList: document.getElementById('subject-list'),
                linkedList: document.getElementById('linked-list'),
                dropZone: document.getElementById('drop-zone'),
                dropSlotProf: document.getElementById('drop-slot-prof'),
                dropSlotSubject: document.getElementById('drop-slot-subject'),
                searchProf: document.getElementById('search-prof'),
                searchSubject: document.getElementById('search-subject'),
                studentSelect: document.getElementById('student_id'),
                sendBtn: document.getElementById('send-btn'),
            };

            // Initialize Data
            function init() {
                state.professors = allProfessors;
                render();
                setupDragAndDrop();
                setupSearch();
                setupStudentChange();
                setupSearchableDropdowns();
                setupSendButton();
            }

            // --- Rendering ---

            function render() {
                renderProfList();
                renderLinkedList();
                renderDropZone();
                renderSubjectList(); // Re-render subject list to update disabled states
                updateSendButton();
            }

            function renderProfList() {
                const query = els.searchProf.value.toLowerCase();
                els.profList.innerHTML = '';
                
                state.professors
                    .filter(p => (p.name).toLowerCase().includes(query))
                    .forEach(p => {
                        const el = createDraggableItem(p, 'professor');
                        els.profList.appendChild(el);
                    });
            }

            function renderSubjectList() {
                els.subjectList.innerHTML = '';
                
                if (!state.selectedStudentId) {
                    els.subjectList.innerHTML = '<p class="text-gray-500 text-sm text-center mt-4">Odaberi studenta da vidiš predmete</p>';
                    els.searchSubject.disabled = true;
                    return;
                }
                
                els.searchSubject.disabled = false;
                const query = els.searchSubject.value.toLowerCase();

                state.subjects
                    .filter(s => s.naziv && s.naziv.toLowerCase().includes(query)) // Ensure s.naziv exists
                    .forEach(s => {
                        const el = createDraggableItem(s, 'subject');
                        
                        // Check if already linked (pending or new match)
                        const isLinked = state.linkedPairs.some(p => p.subject.id === s.id);
                        if (isLinked) {
                            el.classList.add('opacity-50', 'cursor-not-allowed', 'bg-gray-100');
                            el.classList.remove('bg-white', 'hover:border-indigo-400', 'bg-green-100', 'border-green-300'); // Remove other styles
                            el.draggable = false;
                            el.title = "Već povezano ili na čekanju";
                            // Remove event listeners by cloning (simplest way to strip them if we didn't want them, but draggable=false handles most)
                            // Actually, just setting draggable false is enough to stop dragstart.
                        } else {
                            // Only apply previous match styling if NOT linked yet
                            if (previousMatches.hasOwnProperty(s.id)) {
                                const match = previousMatches[s.id];
                                el.classList.remove('bg-white'); // Remove default white background
                                el.classList.add('bg-green-100', 'border-green-300'); // Stronger green
                                
                                const actionsDiv = document.createElement('div');
                                actionsDiv.className = 'flex items-center space-x-2 ml-2';
                                
                                const linkBtn = document.createElement('button');
                                linkBtn.type = 'button';
                                linkBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600 hover:text-green-800" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>`;
                                linkBtn.title = "Auto-match na " + match.fit_predmet_name;
                                linkBtn.onclick = (e) => {
                                    e.stopPropagation();
                                    autoMatch(s, match);
                                };
                                
                                 const infoIcon = document.createElement('div');
                                 infoIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500 cursor-pointer hover:text-blue-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>`;
                                 infoIcon.onclick = (e) => {
                                     e.stopPropagation();
                                     showMatchInfo(s.id);
                                 };
                                 
                                 actionsDiv.appendChild(linkBtn);
                                 actionsDiv.appendChild(infoIcon);
                                 el.appendChild(actionsDiv);
                             }
                         }

                        els.subjectList.appendChild(el);
                    });
            }

            function renderLinkedList() {
                els.linkedList.innerHTML = '';
                state.linkedPairs.forEach((pair, index) => {
                    const el = document.createElement('div');
                    el.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded shadow-sm text-sm';
                    
                    if (pair.isLocked) {
                         el.className = 'flex items-center justify-between p-3 bg-gray-50 border border-gray-200 rounded shadow-sm text-sm opacity-75';
                    }

                    let matchInfo;
                    if (pair.fit_predmet_name) {
                        matchInfo = `<div class="text-green-600 font-bold">-> ${pair.fit_predmet_name}</div>`;
                    } else {
                        matchInfo = `<div class="text-gray-600">-> ${pair.prof.name}</div>`;
                    }
                    
                    if (pair.isLocked) {
                        matchInfo += `<div class="text-yellow-600 text-xs font-semibold mt-1">Predmet je već poslat, čeka se odgovor profesora</div>`;
                    }

                    let deleteBtn = `<button type="button" class="ml-3 text-red-500 hover:text-red-700 font-bold px-2" onclick="unlinkPair(${index})">&times;</button>`;
                    if (pair.isLocked) {
                        deleteBtn = `<span class="ml-3 text-gray-400 px-2 cursor-not-allowed" title="Ne može se ukloniti povezivanje na čekanju">&times;</span>`;
                    }

                    el.innerHTML = `
                        <div class="flex-1">
                            <div class="font-medium text-gray-800" title="${pair.subject.naziv}">${pair.subject.naziv}</div>
                            ${matchInfo}
                        </div>
                        ${deleteBtn}
                    `;
                    els.linkedList.appendChild(el);
                });
            }

            function renderDropZone() {
                if (state.pendingProf) {
                    els.dropSlotProf.textContent = state.pendingProf.name;
                    els.dropSlotProf.className = "w-1/2 h-12 bg-indigo-50 border border-indigo-300 text-indigo-700 rounded flex items-center justify-center text-xs text-center px-2 font-medium relative group cursor-pointer";
                    els.dropSlotProf.innerHTML += `<span class="hidden group-hover:flex absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 items-center justify-center text-[10px]" onclick="clearSlot('professor', event)">x</span>`;
                } else {
                    els.dropSlotProf.textContent = "Profesori";
                    els.dropSlotProf.className = "w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2";
                }

                if (state.pendingSubject) {
                    els.dropSlotSubject.textContent = state.pendingSubject.naziv;
                    els.dropSlotSubject.className = "w-1/2 h-12 bg-indigo-50 border border-indigo-300 text-indigo-700 rounded flex items-center justify-center text-xs text-center px-2 font-medium relative group cursor-pointer";
                    els.dropSlotSubject.innerHTML += `<span class="hidden group-hover:flex absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 items-center justify-center text-[10px]" onclick="clearSlot('subject', event)">x</span>`;
                } else {
                    els.dropSlotSubject.textContent = "Predmeti";
                    els.dropSlotSubject.className = "w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2";
                }
            }

            function updateSendButton() {
                // Always show if there are pairs, regardless of lock status
                if (state.linkedPairs.length > 0) {
                    els.sendBtn.classList.remove('hidden');
                } else {
                    els.sendBtn.classList.add('hidden');
                }
            }

            function createDraggableItem(item, type) {
                const div = document.createElement('div');
                div.className = 'draggable-item bg-white p-2 rounded border border-gray-200 shadow-sm text-sm hover:border-indigo-400 transition-colors flex justify-between items-center group';
                div.draggable = true;
                div.dataset.id = item.id;
                div.dataset.type = type;
                
                // Content
                const content = document.createElement('span');
                if (type === 'professor') {
                     content.textContent = item.name;
                } else {
                     content.textContent = `${item.naziv} (${item.ects} ECTS)`;
                }
                div.appendChild(content);
                
                div.addEventListener('dragstart', (e) => {
                    div.classList.add('dragging');
                    e.dataTransfer.setData('text/plain', JSON.stringify({ id: item.id, type: type }));
                    e.dataTransfer.effectAllowed = 'move';
                });

                div.addEventListener('dragend', () => {
                    div.classList.remove('dragging');
                });

                return div;
            }

            // --- Logic ---

            async function loadStudentSubjects(studentId) {
                try {
                    const response = await fetch(`{{ url('/admin/prepisi/student-subjects') }}/${studentId}`);
                    if (!response.ok) throw new Error('Failed to fetch subjects');
                    const data = await response.json();
                    state.subjects = data;
                    
                    // Populate pending matches (Global)
                    state.subjects.forEach(subject => {
                        // Check if this subject is pending GLOBALLY (for any student)
                        // If so, we auto-add it to the list for THIS student too, so it gets sent to the same professor.
                        // BUT, if we already have a PREVIOUS MATCH (accepted/known), we prioritize that (Auto-match)
                        // so the admin can use the known match instead of waiting.
                        if (globalPendingMatches.hasOwnProperty(subject.id) && !previousMatches.hasOwnProperty(subject.id)) {
                             const matchData = globalPendingMatches[subject.id];
                             const prof = allProfessors.find(p => p.id == matchData.professor_id);
                             
                             if (prof) {
                                 // Check if already added (to avoid duplicates if re-rendering)
                                 if (!state.linkedPairs.some(p => p.subject.id == subject.id)) {
                                     state.linkedPairs.push({
                                         prof: prof,
                                         subject: subject,
                                         isLocked: true // Locked because it's dictated by the global pending state
                                     });
                                 }
                             }
                        }
                    });
                    
                    renderSubjectList();
                    renderLinkedList(); // Render linked list with pending items
                    updateSendButton(); // Ensure button is shown if there are pending items
                } catch (error) {
                    console.error('Error fetching subjects:', error);
                    alert('Nije moguće učitati predmete studenta.');
                }
            }
            
            function pairExists(profId, subjectId) {
                return state.linkedPairs.some(p => p.prof.id == profId && p.subject.id == subjectId);
            }

            window.unlinkPair = function(index) {
                state.linkedPairs.splice(index, 1);
                render();
            };

            window.clearSlot = function(type, e) {
                e.stopPropagation();
                if (type === 'professor') state.pendingProf = null;
                if (type === 'subject') state.pendingSubject = null;
                render();
            };

            function autoMatch(subject, matchData) {
                const prof = allProfessors.find(p => p.id == matchData.professor_id);
                if (!prof) {
                    alert('Profesor za ovaj par nije pronađen.');
                    return;
                }

                if (!pairExists(prof.id, subject.id)) {
                    state.linkedPairs.push({ 
                        prof: prof, 
                        subject: subject,
                        fit_predmet_id: matchData.fit_predmet_id, // Store the FIT subject ID
                        fit_predmet_name: matchData.fit_predmet_name // Store name for display
                    });
                    render();
                } else {
                    alert('Ovo povezivanje već postoji.');
                }
            }

            window.showMatchInfo = function(subjectId) {
                const match = previousMatches[subjectId];
                if (!match) return;

                const content = document.getElementById('matchInfoContent');
                content.innerHTML = `
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <svg class="h-6 w-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Prethodno povezano</p>
                            <p class="text-sm text-gray-500">Ovaj predmet ima verifikovanog ekvivalenta u sistemu.</p>
                        </div>
                    </div>
                    <div class="mt-4 border-t border-gray-100 pt-4">
                        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-2">
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Povezao/la</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-medium">${match.professor_name}</dd>
                            </div>
                            <div class="sm:col-span-1">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Datum</dt>
                                <dd class="mt-1 text-sm text-gray-900 font-medium">${match.date}</dd>
                            </div>
                            <div class="sm:col-span-2">
                                <dt class="text-xs font-semibold text-gray-500 uppercase tracking-wider">FIT Ekvivalent</dt>
                                <dd class="mt-1 text-sm text-indigo-600 font-bold">${match.fit_predmet_name}</dd>
                            </div>
                        </dl>
                    </div>
                `;

                const modal = document.getElementById('matchInfoModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                
                // Close on click outside
                const closeHandler = (e) => {
                    if (e.target === modal) {
                        closeMatchModal();
                        modal.removeEventListener('click', closeHandler);
                    }
                };
                modal.addEventListener('click', closeHandler);
            };

            window.closeMatchModal = function() {
                const modal = document.getElementById('matchInfoModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            };

            // --- Drag and Drop Setup ---

            function setupDragAndDrop() {
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
                        
                        if (itemData.type === 'professor') {
                            const prof = allProfessors.find(p => p.id == itemData.id);
                             if (prof) state.pendingProf = prof;
                        } else { // subject
                             const subj = state.subjects.find(s => s.id == itemData.id);
                             if (subj) state.pendingSubject = subj;
                        }

                        if (state.pendingProf && state.pendingSubject) {
                            if (!pairExists(state.pendingProf.id, state.pendingSubject.id)) {
                                state.linkedPairs.push({ prof: state.pendingProf, subject: state.pendingSubject });
                                state.pendingProf = null;
                                state.pendingSubject = null;
                            } else {
                                alert('Ovo povezivanje već postoji.');
                            }
                        }

                        render();

                    } catch (err) {
                        console.error('Drop error', err);
                    }
                });
            }

            // --- Search & Events ---

            function setupSearch() {
                els.searchProf.addEventListener('input', renderProfList);
                els.searchSubject.addEventListener('input', renderSubjectList);
            }

            function setupStudentChange() {
                els.studentSelect.addEventListener('change', () => {
                    const studentId = els.studentSelect.value;
                    state.selectedStudentId = studentId;
                    state.pendingSubject = null;
                    state.linkedPairs = []; 
                    
                    if (studentId) {
                        loadStudentSubjects(studentId);
                    } else {
                        state.subjects = [];
                        renderSubjectList();
                        render(); // Clear linked list
                    }
                });
            }

            function setupSearchableDropdowns() {
                 document.querySelectorAll('.searchable-container').forEach(container => {
                    const input = container.querySelector('.search-input');
                    const resultsDiv = container.querySelector('.search-results');
                    const select = container.querySelector('select');
                    
                    let optionsData = Array.from(select.options)
                        .filter(opt => opt.value)
                        .map(opt => ({
                            id: opt.value,
                            text: opt.dataset.text || opt.text,
                            element: opt
                        }));

                    if (select.value) {
                         const selected = optionsData.find(o => o.id === select.value);
                         if (selected) input.value = selected.text;
                    }

                    input.addEventListener('input', () => {
                        const query = input.value.toLowerCase();
                        const filtered = optionsData.filter(o => o.text.toLowerCase().includes(query));
                        
                        resultsDiv.innerHTML = '';
                        if (filtered.length === 0) {
                            resultsDiv.innerHTML = '<div class="px-4 py-2 text-gray-500 italic">No results found</div>';
                        } else {
                            filtered.forEach(res => {
                                const div = document.createElement('div');
                                div.className = 'px-4 py-2 hover:bg-blue-50 cursor-pointer transition-colors';
                                div.textContent = res.text;
                                div.addEventListener('click', () => {
                                    input.value = res.text;
                                    select.value = res.id;
                                    select.dispatchEvent(new Event('change'));
                                    resultsDiv.classList.add('hidden');
                                });
                                resultsDiv.appendChild(div);
                            });
                        }
                        resultsDiv.classList.remove('hidden');
                    });

                    input.addEventListener('focus', () => {
                        input.dispatchEvent(new Event('input'));
                    });

                    document.addEventListener('click', (e) => {
                        if (!container.contains(e.target)) {
                            resultsDiv.classList.add('hidden');
                        }
                    });
                });
            }

            function setupSendButton() {
                els.sendBtn.addEventListener('click', async () => {
                    // Send ALL pairs, including locked ones (as they need to be created for this student)
                    if (state.linkedPairs.length === 0) {
                        alert('Nema parova za slanje.');
                        return;
                    }
                    
                    if (!confirm('Jeste li sigurni da želite da sačuvate ove parove?')) return;

                    const matches = state.linkedPairs.map(p => ({
                        professor_id: p.prof.id,
                        subject_id: p.subject.id,
                        fit_predmet_id: p.fit_predmet_id || null // Send FIT subject ID if available
                    }));

                    try {
                        const response = await fetch('{{ route("prepis.match.store") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                student_id: state.selectedStudentId,
                                matches: matches
                            })
                        });

                        if (response.ok) {
                            alert('Uspješno sačuvano!');
                            window.location.href = "{{ route('prepis.index') }}";
                        } else {
                            alert('Greška!');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Došlo je do greške.');
                    }
                });
            }

            init();
        });
    </script>
</x-app-layout>
