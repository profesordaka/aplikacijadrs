<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Izmijeni Prepis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <a href="{{ route('prepis.index') }}" class="text-blue-600 hover:text-blue-800 font-semibold">
                            &larr; Nazad na upravljanje Prepisima
                        </a>
                    </div>

                    <form action="{{ route('prepis.update', $prepis->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Top Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                            <div>
                                <label for="student_id" class="block text-sm font-medium text-gray-700">Student</label>
                                <div class="relative searchable-container" data-type="student">
                                    <input type="text" class="search-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Search Student..." autocomplete="off">
                                    <div class="search-results absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden"></div>
                                    <select name="student_id" id="student_id" class="hidden" required>
                                        <option value="">Izaberi studenta</option>
                                        @foreach($studenti as $student)
                                            <option value="{{ $student->id }}" 
                                                {{ $prepis->student_id == $student->id ? 'selected' : '' }}
                                                data-text="{{ $student->ime }} {{ $student->prezime }} ({{ $student->br_indexa }})">
                                                {{ $student->ime }} {{ $student->prezime }} ({{ $student->br_indexa }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="fakultet_id" class="block text-sm font-medium text-gray-700">Faculty</label>
                                <div class="relative searchable-container" data-type="faculty">
                                    <input type="text" class="search-input mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="Search Faculty..." autocomplete="off">
                                    <div class="search-results absolute z-50 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-60 overflow-y-auto hidden"></div>
                                    <select name="fakultet_id" id="fakultet_id" class="hidden" required>
                                        <option value="">Odaberi fakultet</option>
                                        @foreach($fakulteti as $fakultet)
                                            <option value="{{ $fakultet->id }}" 
                                                {{ $prepis->fakultet_id == $fakultet->id ? 'selected' : '' }}
                                                data-text="{{ $fakultet->naziv }}">
                                                {{ $fakultet->naziv }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label for="datum" class="block text-sm font-medium text-gray-700">Date</label>
                                <input type="date" name="datum" id="datum" value="{{ $prepis->datum ? $prepis->datum->format('Y-m-d') : '' }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
                            </div>
                        </div>

                        <!-- Drag and Drop Interface -->
                        <div class="mb-6 select-none">
                            <h3 class="text-lg font-medium mb-4">Poveži predmete</h3>
                            
                            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                                <!-- FIT Subjects Column -->
                                <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                    <h4 class="font-semibold text-gray-700 mb-2">Dostupni predmeti sa FITa</h4>
                                    <input type="text" id="search-fit" placeholder="Search FIT..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                    <div id="fit-list" class="h-[500px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                        <!-- FIT Items will be injected here -->
                                    </div>
                                </div>

                                <!-- Drop Zone & Linked List -->
                                <div class="flex flex-col space-y-4">
                                    <!-- Drop Zone -->
                                    <div id="drop-zone" class="bg-blue-50 border-2 border-dashed border-blue-300 rounded-lg p-6 flex flex-col items-center justify-center transition-colors min-h-[150px]">
                                        <p class="text-blue-500 font-medium text-center mb-2">Prevuci ovdje predmete da povežeš</p>
                                        <div class="flex items-center space-x-4 w-full justify-center">
                                            <div id="drop-slot-fit" class="w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2">
                                                FIT Predmeti
                                            </div>
                                            <span class="text-gray-400">+</span>
                                            <div id="drop-slot-foreign" class="w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2">
                                                Strani Predmeti
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Linked List -->
                                    <div class="flex-1 bg-white rounded-lg border border-gray-200 shadow-sm flex flex-col">
                                        <div class="p-3 border-b border-gray-200 bg-gray-50 rounded-t-lg">
                                            <h4 class="font-semibold text-gray-700">Povezani parovi</h4>
                                        </div>
                                        <div id="linked-list" class="h-[350px] overflow-y-auto p-2 space-y-2">
                                            <!-- Linked Pairs will be injected here -->
                                        </div>
                                    </div>
                                </div>

                                <!-- Foreign Subjects Column -->
                                <div class="flex flex-col bg-white rounded-lg p-4 border border-gray-200 shadow-sm">
                                    <h4 class="font-semibold text-gray-700 mb-2">Dostupni strani predmeti</h4>
                                    <input type="text" id="search-foreign" placeholder="Search Foreign..." class="mb-2 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" disabled>
                                    <div id="foreign-list" class="h-[500px] overflow-y-auto space-y-2 p-1 border border-gray-100 rounded bg-gray-50">
                                        <p class="text-gray-500 text-sm text-center mt-4">Izaberi fakultet da viđiš predmete</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden Inputs Container -->
                        <div id="form-inputs"></div>

                        <div class="flex justify-end mt-6 space-x-4">
                            <button type="button" onclick="openPreviewModal()" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded shadow-lg transform transition hover:scale-105">
                                Pregledaj transkript
                            </button>
                            <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg transform transition hover:scale-105">
                                Sačuvaj Prepis
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Preview Modal -->
    <div id="preview-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3 text-center">
                <h3 class="text-lg leading-6 font-medium text-gray-900">Transcript Preview</h3>
                <div class="mt-2 px-7 py-3">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FIT Subject</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ECTS</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foreign Subject</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ECTS</th>
                            </tr>
                        </thead>
                        <tbody id="preview-table-body" class="bg-white divide-y divide-gray-200">
                            <!-- Rows will be populated by JS -->
                        </tbody>
                        <tfoot class="bg-gray-50 font-bold">
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Ukupno:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-left" id="total-fit-ects">0</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Ukupno:</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-left" id="total-foreign-ects">0</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="items-center px-4 py-3">
                    <button id="close-modal" class="px-4 py-2 bg-blue-500 text-white text-base font-medium rounded-md w-full shadow-lg transform transition hover:scale-105 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Close
                    </button>
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
        .automatch-btn {
            display: none;
        }
        .draggable-item:hover .automatch-btn {
            display: flex;
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const allSubjects = @json($predmeti);
            const existingAgreements = @json($existingAgreements);
            const existingAgreementsForeign = @json($existingAgreementsForeign);
            
            // Loaded from existing Prepis
            const initialAgreements = @json($prepis->agreements);
            const initialFacultyId = "{{ $prepis->fakultet_id }}";

            // State
            let state = {
                fitSubjects: [],
                foreignSubjects: [],
                linkedPairs: [],
                pendingFit: null,
                pendingForeign: null,
                selectedFacultyId: initialFacultyId
            };

            // DOM Elements
            const els = {
                fitList: document.getElementById('fit-list'),
                foreignList: document.getElementById('foreign-list'),
                linkedList: document.getElementById('linked-list'),
                dropZone: document.getElementById('drop-zone'),
                dropSlotFit: document.getElementById('drop-slot-fit'),
                dropSlotForeign: document.getElementById('drop-slot-foreign'),
                searchFit: document.getElementById('search-fit'),
                searchForeign: document.getElementById('search-foreign'),
                facultySelect: document.getElementById('fakultet_id'),
                formInputs: document.getElementById('form-inputs'),
                previewModal: document.getElementById('preview-modal'),
                previewTableBody: document.getElementById('preview-table-body'),
                closeModalBtn: document.getElementById('close-modal'),
                totalFitEcts: document.getElementById('total-fit-ects'),
                totalForeignEcts: document.getElementById('total-foreign-ects'),
            };

            // Initialize Data
            function init() {
                state.fitSubjects = allSubjects;
                
                // Initialize linked pairs from existing data
                if (initialAgreements && initialAgreements.length > 0) {
                    initialAgreements.forEach(agreement => {
                        const fitSub = allSubjects.find(s => s.id == agreement.fit_predmet_id);
                        const foreignSub = allSubjects.find(s => s.id == agreement.strani_predmet_id);
                        if (fitSub && foreignSub) {
                            state.linkedPairs.push({ fit: fitSub, foreign: foreignSub });
                        }
                    });
                }
                
                filterForeignSubjects();
                
                render();
                setupDragAndDrop();
                setupSearch();
                setupFacultyChange();
                setupStudentSearch();
                setupModal();
            }

            // --- Rendering ---

            function render() {
                renderFitList();
                renderForeignList();
                renderLinkedList();
                renderDropZone();
                updateFormInputs();
            }

            function renderFitList() {
                const query = els.searchFit.value.toLowerCase();
                els.fitList.innerHTML = '';
                
                state.fitSubjects
                    .filter(s => s.naziv.toLowerCase().includes(query))
                    .forEach(s => {
                        const hasMatches = existingAgreements[s.id] && existingAgreements[s.id].length > 0;
                        const el = createDraggableItem(s, 'fit', hasMatches);
                        els.fitList.appendChild(el);
                    });
            }

            function renderForeignList() {
                els.foreignList.innerHTML = '';
                
                if (!state.selectedFacultyId) {
                    els.foreignList.innerHTML = '<p class="text-gray-500 text-sm text-center mt-4">Select a faculty to view subjects</p>';
                    els.searchForeign.disabled = true;
                    return;
                }
                
                els.searchForeign.disabled = false;
                const query = els.searchForeign.value.toLowerCase();

                state.foreignSubjects
                    .filter(s => s.naziv.toLowerCase().includes(query))
                    .forEach(s => {
                        const hasMatches = existingAgreementsForeign[s.id] && existingAgreementsForeign[s.id].length > 0;
                        const el = createDraggableItem(s, 'foreign', hasMatches);
                        els.foreignList.appendChild(el);
                    });
            }

            function renderLinkedList() {
                els.linkedList.innerHTML = '';
                state.linkedPairs.forEach((pair, index) => {
                    const el = document.createElement('div');
                    el.className = 'flex items-center justify-between p-3 bg-white border border-gray-200 rounded shadow-sm text-sm';
                    el.innerHTML = `
                        <div class="flex-1 grid grid-cols-2 gap-2">
                            <div class="font-medium text-gray-800 truncate" title="${pair.fit.naziv}">${pair.fit.naziv}</div>
                            <div class="text-gray-600 truncate" title="${pair.foreign.naziv}">${pair.foreign.naziv}</div>
                        </div>
                        <button type="button" class="ml-3 text-red-500 hover:text-red-700 font-bold px-2" onclick="unlinkPair(${index})">&times;</button>
                    `;
                    els.linkedList.appendChild(el);
                });
            }

            function renderDropZone() {
                if (state.pendingFit) {
                    els.dropSlotFit.textContent = state.pendingFit.naziv;
                    els.dropSlotFit.className = "w-1/2 h-12 bg-indigo-50 border border-indigo-300 text-indigo-700 rounded flex items-center justify-center text-xs text-center px-2 font-medium relative group cursor-pointer";
                    els.dropSlotFit.innerHTML += `<span class="hidden group-hover:flex absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 items-center justify-center text-[10px]" onclick="clearSlot('fit', event)">x</span>`;
                } else {
                    els.dropSlotFit.textContent = "FIT Subject";
                    els.dropSlotFit.className = "w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2";
                }

                if (state.pendingForeign) {
                    els.dropSlotForeign.textContent = state.pendingForeign.naziv;
                    els.dropSlotForeign.className = "w-1/2 h-12 bg-indigo-50 border border-indigo-300 text-indigo-700 rounded flex items-center justify-center text-xs text-center px-2 font-medium relative group cursor-pointer";
                    els.dropSlotForeign.innerHTML += `<span class="hidden group-hover:flex absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-4 h-4 items-center justify-center text-[10px]" onclick="clearSlot('foreign', event)">x</span>`;
                } else {
                    els.dropSlotForeign.textContent = "Foreign Subject";
                    els.dropSlotForeign.className = "w-1/2 h-12 bg-white border border-gray-200 rounded flex items-center justify-center text-xs text-gray-400 text-center px-2";
                }
            }

            function createDraggableItem(subject, type, hasMatches = false) {
                const div = document.createElement('div');
                div.className = 'draggable-item bg-white p-2 rounded border border-gray-200 shadow-sm text-sm hover:border-indigo-400 transition-colors flex justify-between items-center group';
                div.draggable = true;
                div.dataset.id = subject.id;
                div.dataset.type = type;
                
                // Content
                const content = document.createElement('span');
                content.textContent = `${subject.naziv} (${subject.ects} ECTS)`;
                div.appendChild(content);

                // Automatch Button (Only if matches exist)
                if (hasMatches) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'automatch-btn hidden group-hover:flex items-center justify-center bg-green-100 hover:bg-green-200 text-green-700 rounded-full p-1 ml-2 transition-colors';
                    btn.title = 'Auto Match with known subjects';
                    btn.innerHTML = `
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                    `;
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        e.preventDefault();
                        automatchSubject(subject, type);
                    });
                    div.appendChild(btn);
                }
                
                div.addEventListener('dragstart', (e) => {
                    div.classList.add('dragging');
                    e.dataTransfer.setData('text/plain', JSON.stringify({ id: subject.id, type: type }));
                    e.dataTransfer.effectAllowed = 'move';
                });

                div.addEventListener('dragend', () => {
                    div.classList.remove('dragging');
                });

                return div;
            }

            // --- Logic ---

            function filterForeignSubjects() {
                if (!state.selectedFacultyId) {
                    state.foreignSubjects = [];
                } else {
                    state.foreignSubjects = allSubjects.filter(s => s.fakultet_id == state.selectedFacultyId);
                }
                renderForeignList();
            }
            
            function pairExists(fitId, foreignId) {
                return state.linkedPairs.some(p => p.fit.id == fitId && p.foreign.id == foreignId);
            }

            window.unlinkPair = function(index) {
                state.linkedPairs.splice(index, 1);
                render();
            };

            window.clearSlot = function(type, e) {
                e.stopPropagation();
                if (type === 'fit') state.pendingFit = null;
                if (type === 'foreign') state.pendingForeign = null;
                render();
            };

            function automatchSubject(subject, type) {
                let matchedCount = 0;

                if (type === 'fit') {
                    if (!existingAgreements[subject.id]) return;
                    const foreignIds = existingAgreements[subject.id];

                    foreignIds.forEach(foreignId => {
                        const foreignSubject = state.foreignSubjects.find(s => s.id == foreignId);
                        if (foreignSubject) {
                            if (!pairExists(subject.id, foreignSubject.id)) {
                                state.linkedPairs.push({ fit: subject, foreign: foreignSubject });
                                matchedCount++;
                            }
                        }
                    });
                } else if (type === 'foreign') {
                    if (!existingAgreementsForeign[subject.id]) return;
                    const fitIds = existingAgreementsForeign[subject.id];

                    fitIds.forEach(fitId => {
                         // Find FIT subject in available list
                         const fitSubject = state.fitSubjects.find(s => s.id == fitId);
                         if (fitSubject) {
                             if (!pairExists(fitSubject.id, subject.id)) {
                                 state.linkedPairs.push({ fit: fitSubject, foreign: subject });
                                 matchedCount++;
                             }
                         }
                    });
                }

                if (matchedCount > 0) {
                    render();
                } else {
                    alert('No matching subjects found in the currently available lists.');
                }
            }

            function updateFormInputs() {
                els.formInputs.innerHTML = '';
                state.linkedPairs.forEach((pair, index) => {
                    const inputFit = document.createElement('input');
                    inputFit.type = 'hidden';
                    inputFit.name = `agreements[${index}][fit_predmet_id]`;
                    inputFit.value = pair.fit.id;
                    
                    const inputForeign = document.createElement('input');
                    inputForeign.type = 'hidden';
                    inputForeign.name = `agreements[${index}][strani_predmet_id]`;
                    inputForeign.value = pair.foreign.id;

                    els.formInputs.appendChild(inputFit);
                    els.formInputs.appendChild(inputForeign);
                });
            }

            // --- Modal Logic ---

            window.openPreviewModal = function() {
                els.previewTableBody.innerHTML = '';
                let totalFit = 0;
                let totalForeign = 0;

                state.linkedPairs.forEach(pair => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-left">${pair.fit.naziv}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-left">${pair.fit.ects}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-left">${pair.foreign.naziv}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-left">${pair.foreign.ects}</td>
                    `;
                    els.previewTableBody.appendChild(row);
                    
                    totalFit += parseFloat(pair.fit.ects) || 0;
                    totalForeign += parseFloat(pair.foreign.ects) || 0;
                });

                els.totalFitEcts.textContent = totalFit;
                els.totalForeignEcts.textContent = totalForeign;

                els.previewModal.classList.remove('hidden');
            };

            function setupModal() {
                els.closeModalBtn.addEventListener('click', () => {
                    els.previewModal.classList.add('hidden');
                });

                els.previewModal.addEventListener('click', (e) => {
                    if (e.target === els.previewModal) {
                        els.previewModal.classList.add('hidden');
                    }
                });
            }

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
                        const item = JSON.parse(data);
                        const subject = allSubjects.find(s => s.id == item.id);
                        
                        if (!subject) return;

                        if (item.type === 'fit') {
                            state.pendingFit = subject;
                        } else {
                            state.pendingForeign = subject;
                        }

                        if (state.pendingFit && state.pendingForeign) {
                            if (!pairExists(state.pendingFit.id, state.pendingForeign.id)) {
                                state.linkedPairs.push({ fit: state.pendingFit, foreign: state.pendingForeign });
                                state.pendingFit = null;
                                state.pendingForeign = null;
                            } else {
                                alert('This pair is already linked.');
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
                els.searchFit.addEventListener('input', renderFitList);
                els.searchForeign.addEventListener('input', renderForeignList);
            }

            function setupFacultyChange() {
                els.facultySelect.addEventListener('change', () => {
                    state.selectedFacultyId = els.facultySelect.value;
                    state.pendingForeign = null;
                    filterForeignSubjects();
                    render();
                });
            }

            function setupStudentSearch() {
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

            init();
        });
    </script>
</x-app-layout>
