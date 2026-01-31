<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Mobilnost') }}
    </h2>
    </x-slot> --}}


    <div class="min-h-screen bg-gray-50 py-8">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Success Message -->
            @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
            @endif

            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Kreiraj mobilnost</h1>
            </div>

            <form id="mobilityForm" action="{{ route('admin.mobility.save') }}" method="POST">
                @csrf
                <input type="hidden" name="courses" id="coursesJson">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

                    <!-- Left Column: Student Info & Mobility -->
                    <div class="lg:col-span-2 space-y-6">

                        <!-- Student & Faculty Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Podaci
                                </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Student Selection -->
                                <div class="relative">
                                    <label for="student_search" class="block text-sm font-medium text-gray-700 mb-1">Izaberi studenta</label>
                                    <div class="relative">
                                        <input type="text" id="student_search" class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-4 pr-10 py-2.5 transition-colors" placeholder="Nađi studenta..." autocomplete="off">
                                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </div>
                                    <input type="hidden" name="student_id" id="student_id">
                                    <div id="student_results" class="hidden absolute z-20 mt-1 w-full bg-white shadow-xl max-h-60 rounded-lg py-1 text-base ring-1 ring-black ring-opacity-5 overflow-auto focus:outline-none sm:text-sm">
                                    </div>
                                </div>

                                <!-- Faculty Selection -->
                                <div>
                                    <label for="fakultet_id" class="block text-sm font-medium text-gray-700 mb-1">Host
                                        fakultet</label>
                                    <select name="fakultet_id" id="fakultet_id" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                        <option value="">-- Izaberi fakultet --</option>
                                        @foreach($fakulteti as $f)
                                        <option value="{{ $f->id }}">{{ $f->naziv }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Dates -->
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Datum početka</label>
                                    <input type="date" name="start_date" id="start_date" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                </div>
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Datum završetka</label>
                                    <input type="date" name="end_date" id="end_date" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                </div>
                            </div>

                            <!-- Mobility Type -->
                            <div class="mt-6">
                                <label for="tip_mobilnosti" class="block text-sm font-medium text-gray-700 mb-1">Tip mobilnosti</label>
                                <select name="tip_mobilnosti" id="tip_mobilnosti" required class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm py-2.5">
                                    <option value="">-- Izaberi tip mobilnosti --</option>
                                    <option value="Semestralna mobilnost">Semestralna mobilnost</option>
                                    <option value="Godišnja mobilnost">Godišnja mobilnost</option>
                                </select>
                            </div>
                        </div>

                        <!-- Subjects Card -->
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2 flex items-center justify-between">
                                <span>Predmeti studenata</span>
                            </h2>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Unpassed Subjects -->
                                <div class="flex flex-col h-full">
                                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Nepoloženi
                                        (Iz prethodne godine)</h3>
                                    <div id="unpassedSubjectsBox" class="flex-1 min-h-[250px] bg-gray-50 rounded-lg border border-gray-200 p-3 overflow-y-auto space-y-2 transition-all hover:border-gray-300">
                                        <div class="flex flex-col items-center justify-center h-full text-gray-400 text-sm">
                                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                                </path>
                                            </svg>
                                            <span>Izaberi studenta</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Next Year Subjects -->
                                <div class="flex flex-col h-full">
                                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-3">Predmeti iz sledeće godine</h3>
                                    <div id="nextYearSubjectsBox" class="flex-1 min-h-[250px] bg-gray-50 rounded-lg border border-gray-200 p-3 overflow-y-auto space-y-2 transition-all hover:border-gray-300">
                                        <div class="flex flex-col items-center justify-center h-full text-gray-400 text-sm">
                                            <svg class="w-8 h-8 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                                                </path>
                                            </svg>
                                            <span>Izaberi studenta</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Column: Available Subjects -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6 h-[calc(100vh-theme('spacing.12'))] flex flex-col">
                            <h2 class="text-lg font-semibold text-gray-800 mb-4 border-b pb-2">Predmeti fakulteta</h2>

                            <div class="mb-4">
                                <input type="text" id="subjectFilter" placeholder="Pretraži predmete..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm py-2 px-3">
                            </div>

                            <div id="available-subjects" class="space-y-3 max-h-[600px] overflow-y-auto pr-2">
                                <div class="p-4 text-center text-gray-500 text-sm">
                                    Izaberi fakultet
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Sticky Bottom Actions -->
                <div class="fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 py-4 px-6 shadow-lg z-30">
                    <div class="max-w-7xl mx-auto flex justify-end gap-4">
                        <button type="button" id="btnExport" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                            </svg>
                            Sačuvaj word dokument
                        </button>

                        <button type="button" id="btnShowSummary" class="inline-flex items-center px-4 py-2 border border-blue-600 shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            <svg class="-ml-1 mr-2 h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            Pregled povezanih predmeta
                        </button>

                        <button type="button" id="btnSave" class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Sačuvaj learning agreement
                        </button>
                    </div>
                </div>

                <!-- Protected Modal -->
                <div id="summaryModal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                        <!-- Background overlay -->
                        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>

                        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                        <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full sm:p-6">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Pregled povezanih predmeta
                                    </h3>
                                    <div class="mt-4 overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                                        <table class="min-w-full divide-y divide-gray-300">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">FIT Predmet</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">FIT ECTS</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Strani predmet</th>
                                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Strani ECTS</th>
                                                </tr>
                                            </thead>
                                            <tbody id="summaryTableBody" class="divide-y divide-gray-200 bg-white">
                                                <!-- Dynamic Rows -->
                                            </tbody>
                                            <tfoot class="bg-gray-50">
                                                <tr>
                                                    <th scope="row" class="pl-4 pr-3 py-4 text-left text-sm font-semibold text-gray-900 sm:pl-6">Ukupno FIT ECTS:</th>
                                                    <td class="pl-3 pr-3 py-4 text-left text-sm font-bold text-indigo-600" id="totalFitEcts">0</td>
                                                    <th scope="row" class="pl-3 pr-3 py-4 text-left text-sm font-semibold text-gray-900">Ukupno Strani ECTS:</th>
                                                    <td class="pl-3 pr-3 py-4 text-left text-sm font-bold text-green-600" id="totalForeignEcts">0</td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-5 sm:mt-4 sm:flex sm:flex-row-reverse">
                                <button type="button" id="btnCloseModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                    Zatvori
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Styles for generated elements --}}
    <style>
        .highlight-active {
            border-color: #6366f1 !important;
            /* Indigo-500 */
            background-color: #eef2ff !important;
            /* Indigo-50 */
            box-shadow: 0 0 0 1px #6366f1;
        }

        .pill-item {
            animation: fadeIn 0.3s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(5px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

    </style>

    <script>
        // State management
        const currentMappings = {};
        let activeLeft = null;

        // Pass students to JS for client-side search
        // (Assuming list isn't massive, otherwise AJAX search is better. 
        // Given the task, I'll stick to client-side filtering of this list for responsiveness).
        const localStudents = @json($students);

        document.addEventListener('DOMContentLoaded', () => {
            const studentSearch = document.getElementById('student_search');
            const studentResults = document.getElementById('student_results');
            const studentIdInput = document.getElementById('student_id');
            const facultySelect = document.getElementById('fakultet_id');
            const availableSubjectsContainer = document.getElementById('available-subjects');
            const subjectFilterInput = document.getElementById('subjectFilter');

            // --- 1. Student Search Logic ---
            studentSearch.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                if (query.length < 1) {
                    studentResults.classList.add('hidden');
                    return;
                }

                const filtered = localStudents.filter(s =>
                    s.ime.toLowerCase().includes(query) ||
                    s.prezime.toLowerCase().includes(query) ||
                    (s.br_indexa && s.br_indexa.toLowerCase().includes(query))
                );

                renderResults(filtered);
            });

            // Show all on focus if empty
            studentSearch.addEventListener('focus', function() {
                if (this.value.trim() === '') {
                    // optionally show recent or all, for now passing empty query to logic 
                    // or just showing first 10
                    renderResults(localStudents.slice(0, 10));
                }
            });

            function renderResults(list) {
                studentResults.innerHTML = '';
                if (list.length === 0) {
                    const noRes = document.createElement('div');
                    noRes.className = 'p-3 text-sm text-gray-500 italic';
                    noRes.textContent = 'No students found.';
                    studentResults.appendChild(noRes);
                    studentResults.classList.remove('hidden');
                    return;
                }

                list.forEach(student => {
                    const div = document.createElement('div');
                    div.className = 'cursor-pointer hover:bg-gray-100 p-2 border-b last:border-0 border-gray-100 transition-colors';
                    div.innerHTML = `
                    <div class="font-medium text-gray-800 text-sm">${student.ime} ${student.prezime}</div>
                    <div class="text-xs text-gray-500">${student.br_indexa || 'No Index'}</div>
                `;
                    div.addEventListener('click', () => {
                        studentSearch.value = `${student.ime} ${student.prezime}`;
                        studentIdInput.value = student.id;
                        studentResults.classList.add('hidden');
                        fetchStudentSubjects(student.id); // Trigger fetch
                    });
                    studentResults.appendChild(div);
                });
                studentResults.classList.remove('hidden');
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!studentSearch.contains(e.target) && !studentResults.contains(e.target)) {
                    studentResults.classList.add('hidden');
                }
            });


            // --- 2. Subject Fetching Logic (Left Side) ---
            async function fetchStudentSubjects(studentId) {
                const unpassedBox = document.getElementById('unpassedSubjectsBox');
                const nextYearBox = document.getElementById('nextYearSubjectsBox');

                // Loading state
                [unpassedBox, nextYearBox].forEach(box => {
                    box.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-indigo-400 animate-pulse">
                        <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        <span class="text-xs">Loading subjects...</span>
                    </div>
                `;
                });

                try {
                    const res = await fetch(`{{ route('admin.mobility.student-subjects') }}?student_id=${studentId}`);
                    if (!res.ok) throw new Error('Network error');
                    const data = await res.json();

                    renderSubjectBox(unpassedBox, data.unpassed, 'unpassed');
                    renderSubjectBox(nextYearBox, data.next_year, 'nextYear');

                } catch (err) {
                    console.error(err);
                    [unpassedBox, nextYearBox].forEach(box => {
                        box.innerHTML = '<span class="text-red-500 text-sm p-2">Error loading subjects.</span>';
                    });
                }
            }

            function renderSubjectBox(container, subjects, type) {
                container.innerHTML = '';
                if (!subjects || subjects.length === 0) {
                    container.innerHTML = `
                    <div class="flex flex-col items-center justify-center h-full text-gray-400 text-sm">
                        <span>No subjects found for this category.</span>
                    </div>
                `;
                    return;
                }

                subjects.forEach(subj => {
                    // Using subject name as key for logic compatibility
                    const key = subj.naziv;

                    const div = document.createElement('div');
                    div.className = 'bg-white border border-gray-200 rounded-md p-3 cursor-pointer hover:shadow-md hover:border-indigo-300 transition-all duration-200 group relative';
                    div.dataset.name = key;
                    div.dataset.id = subj.id;
                    div.dataset.ects = subj.ects;

                    div.innerHTML = `
                    <div class="flex items-start justify-between">
                        <span class="text-sm font-medium text-gray-700 group-hover:text-indigo-700">${subj.naziv} <span class="text-xs text-gray-500 font-normal">(${subj.ects} ECTS)</span></span>
                        <div class="h-2 w-2 rounded-full bg-indigo-100 group-hover:bg-indigo-500 transition-colors"></div>
                    </div>
                    <div class="linked-pills mt-2 flex flex-wrap gap-1"></div>
                `;

                    div.addEventListener('click', () => {
                        // Visual Toggle
                        if (activeLeft === div) {
                            setActiveLeft(null);
                        } else {
                            setActiveLeft(div);
                        }
                    });

                    // If we already have mappings for this subject (e.g. re-render), restore them
                    if (currentMappings[key] && currentMappings[key].length > 0) {
                        renderPills(div);
                    }

                    container.appendChild(div);
                });
            }

            function setActiveLeft(el) {
                // Remove active class from old
                if (activeLeft) {
                    activeLeft.classList.remove('highlight-active', 'ring-2', 'ring-indigo-500');
                }
                activeLeft = el;
                if (activeLeft) {
                    activeLeft.classList.add('highlight-active', 'ring-2', 'ring-indigo-500');
                }
            }

            // --- 3. Faculty Filter Subjects (Right Side) ---
            facultySelect.addEventListener('change', function() {
                const facultyId = this.value;
                if (facultyId) {
                    fetchFacultySubjects(facultyId);
                } else {
                    // Clear or show all? Request said "appear when faculty is selected"
                    // Let's clear or show placeholder
                    availableSubjectsContainer.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">Select a faculty to see subjects.</div>';
                }
            });

            async function fetchFacultySubjects(facultyId) {
                availableSubjectsContainer.innerHTML = `
                <div class="flex flex-col items-center justify-center h-40 text-indigo-400 animate-pulse">
                     <span class="text-sm">Fetching faculty subjects...</span>
                </div>
            `;

                try {
                    const res = await fetch(`{{ route('admin.mobility.faculty-subjects') }}?fakultet_id=${facultyId}`);
                    if (!res.ok) throw new Error('Network error');
                    const subjects = await res.json();
                    renderAvailableSubjects(subjects);
                } catch (err) {
                    console.error(err);
                    availableSubjectsContainer.innerHTML = '<div class="p-4 text-center text-red-500 text-sm">Error loading subjects.</div>';
                }
            }

            function renderAvailableSubjects(subjects) {
                availableSubjectsContainer.innerHTML = '';
                if (!subjects || subjects.length === 0) {
                    availableSubjectsContainer.innerHTML = '<div class="p-4 text-center text-gray-500 text-sm">No subjects found for this faculty.</div>';
                    return;
                }

                subjects.forEach(p => {
                    const div = document.createElement('div');
                    div.className = 'available-subject group p-3 rounded-lg border border-gray-100 hover:border-indigo-200 hover:bg-indigo-50 cursor-pointer transition-all duration-200 mb-2';
                    div.dataset.id = p.id;
                    div.dataset.name = p.naziv;
                    div.dataset.ects = p.ects;

                    div.innerHTML = `
                    <div class="flex justify-between items-start">
                        <span class="font-medium text-gray-700 group-hover:text-indigo-700 text-sm">${p.naziv} <span class="text-xs text-gray-500 font-normal">(${p.ects} ECTS)</span></span>
                        <span class="hidden group-hover:inline-block text-indigo-500">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        </span>
                    </div>
                `;

                    div.addEventListener('click', () => {
                        if (!activeLeft) {
                            alert('Iaberite predmete za studenta prvo (lijeva strana).');
                            return;
                        }
                        toggleLink(activeLeft, div);
                    });

                    availableSubjectsContainer.appendChild(div);
                });
            }


            // --- 4. Filtering Logic for Right Side (Search input) ---
            subjectFilterInput.addEventListener('input', function(e) { // changed keyup to input for safety
                const match = e.target.value.toLowerCase();
                const items = availableSubjectsContainer.querySelectorAll('.available-subject');
                items.forEach(item => {
                    const name = item.dataset.name.toLowerCase();
                    item.style.display = name.includes(match) ? 'block' : 'none';
                });
            });


            // --- 5. Linking Logic ---
            function toggleLink(leftDiv, rightDiv) {
                const leftName = leftDiv.dataset.name;
                const rightId = rightDiv.dataset.id;
                // rightDiv might be newly created, need to grab name correctly
                const rightName = rightDiv.dataset.name;
                const rightEcts = rightDiv.dataset.ects;

                if (!currentMappings[leftName]) {
                    currentMappings[leftName] = [];
                }

                const existsIndex = currentMappings[leftName].findIndex(r => r.id === rightId);

                if (existsIndex >= 0) {
                    // Remove
                    currentMappings[leftName].splice(existsIndex, 1);
                } else {
                    // Add
                    currentMappings[leftName].push({
                        id: rightId,
                        name: rightName,
                        ects: rightEcts
                    });
                }

                renderPills(leftDiv);
            }

            function renderPills(leftDiv) {
                const container = leftDiv.querySelector('.linked-pills');
                const leftName = leftDiv.dataset.name;
                const mappings = currentMappings[leftName] || [];

                container.innerHTML = '';
                mappings.forEach(m => {
                    const span = document.createElement('span');
                    span.className = 'pill-item inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800 mr-1 mb-1';
                    span.innerHTML = `
                    ${m.name}
                    <button type="button" class="ml-1 text-indigo-400 hover:text-indigo-600 focus:outline-none">
                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                    </button>
                `;

                    span.querySelector('button').addEventListener('click', (e) => {
                        e.stopPropagation();
                        const idx = currentMappings[leftName].findIndex(x => x.id === m.id);
                        if (idx !== -1) {
                            currentMappings[leftName].splice(idx, 1);
                            renderPills(leftDiv);
                        }
                    });

                    container.appendChild(span);
                });
            }


            // --- 6. Submission Logic ---
            document.getElementById('btnSave').addEventListener('click', () => {
                submitForm('save');
            });

            document.getElementById('btnExport').addEventListener('click', () => {
                submitForm('export');
            });

            function submitForm(actionStr) {
                const studentId = studentIdInput.value;
                if (!studentId) {
                    alert('Molimo izaberite studenta.');
                    return;
                }
                if (Object.keys(currentMappings).length === 0) {
                    if (!confirm('Nema povezanih predmeta. Želite li da nastavite?')) return;
                }

                // Prepare JSON
                const payload = {};
                for (const [subjName, rights] of Object.entries(currentMappings)) {
                    if (rights.length > 0) {
                        payload[subjName] = rights.map(r => r.id);
                    }
                }

                document.getElementById('coursesJson').value = JSON.stringify(payload);

                const form = document.getElementById('mobilityForm');
                if (actionStr === 'save') {
                    form.action = "{{ route('admin.mobility.save') }}";
                    form.submit();
                } else {
                    form.action = "{{ route('admin.mobility.export') }}";
                    form.submit();
                }
            }

            // --- 7. Modal Logic ---
            const modal = document.getElementById('summaryModal');
            const btnShowSummary = document.getElementById('btnShowSummary');
            const btnCloseModal = document.getElementById('btnCloseModal');
            const summaryTableBody = document.getElementById('summaryTableBody');
            const totalFitEctsEl = document.getElementById('totalFitEcts');
            const totalForeignEctsEl = document.getElementById('totalForeignEcts');

            btnShowSummary.addEventListener('click', () => {
                renderSummaryTable();
                modal.classList.remove('hidden');
            });

            btnCloseModal.addEventListener('click', () => {
                modal.classList.add('hidden');
            });

            // Close on escape
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            });

            function renderSummaryTable() {
                summaryTableBody.innerHTML = '';
                let totalFit = 0;
                let totalForeign = 0;
                let processedForeignIds = new Set();
                
                // Get all FIT subject elements to retrieve their ECTS (since currentMappings only has name)
                // We stored ECTS in dataset of DOM elements.
                // Or we can rebuild a map from what we fetched.
                // Since `currentMappings` keys are names, we find the container.
                
                // Better approach: Iterate keys of currentMappings
                for (const [fitName, foreignList] of Object.entries(currentMappings)) {
                    if (foreignList.length === 0) continue;

                    // Find FIT ECTS
                    // We can look up the element in DOM
                    const fitEl = document.querySelector(`.bg-white[data-name="${CSS.escape(fitName)}"]`);
                    const fitEcts = fitEl ? parseFloat(fitEl.dataset.ects) || 0 : 0;
                    
                    if (foreignList.length > 0) totalFit += fitEcts;

                    foreignList.forEach(f => {
                         if (!processedForeignIds.has(f.id)) {
                             totalForeign += parseFloat(f.ects) || 0;
                             processedForeignIds.add(f.id);
                         }
                    });

                    const foreignNames = foreignList.map(f => `${f.name} (${f.ects})`).join('<br>');
                    const foreignSum = foreignList.reduce((acc, curr) => acc + (parseFloat(curr.ects) || 0), 0);

                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">${fitName}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">${fitEcts}</td>
                        <td class="px-3 py-4 text-sm text-gray-500">${foreignNames}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">${foreignSum}</td>
                    `;
                    summaryTableBody.appendChild(row);
                }
                
                totalFitEctsEl.textContent = totalFit;
                totalForeignEctsEl.textContent = totalForeign;
            }
        });

    </script>
</x-app-layout>
