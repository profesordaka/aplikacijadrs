<x-app-layout>
    @if(session('success'))
    <div class="mb-4 bg-green-100 text-green-800 p-3 rounded-md mx-6 mt-6 max-w-7xl mx-auto">
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-6 mt-6 max-w-7xl mx-auto" role="alert">
        <strong class="font-bold">Upsss!</strong>
        <span class="block">Došlo je do greške:</span>
        <ul class="mt-2 list-disc list-inside">
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Predmeti - {{ $user->name }}</h1>
            <div class="flex space-x-2">
                <a href="{{ route('users.index') }}"
                    class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                    Nazad
                </a>
                <button id="addSubjectBtn"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                    Dodaj Predmet
                </button>
            </div>
        </div>

        <div class="mb-4">
            <input type="text" id="searchSubject" placeholder="Pretraži.."
                class="w-full max-w-md border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Lista Predmeta</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $user->predmeti->count() }} Ukupno</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Naziv</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ECTS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Semestar</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Radnja</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($user->predmeti as $p)
                        <tr class="subject-row hover:bg-gray-50 transition-colors duration-150 ease-in-out" data-search="{{ strtolower($p->naziv) }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ $p->naziv }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->ects }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $p->semestar }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <form action="{{ route('users.subjects.destroy', ['id' => $user->id, 'predmet_id' => $p->id]) }}" method="POST" onsubmit="return confirm('Da li ste sigurni?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors">
                                        Ukloni
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div id="addSubjectModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <h2 class="text-xl font-semibold mb-4 text-gray-800">Dodaj Predmet</h2>

            <form action="{{ route('users.subjects.store', $user->id) }}" method="POST">
                @csrf
                
                <div class="mb-4 relative">
                    <label for="subjectSearchInput" class="block text-gray-700 font-medium mb-1">Pretraži Predmet</label>
                    <input type="text" id="subjectSearchInput" placeholder="Unesite naziv predmeta..." 
                        class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2" autocomplete="off">
                    <div id="subjectSearchResults" class="absolute z-10 w-full bg-white border border-gray-300 rounded-lg shadow-lg mt-1 max-h-40 overflow-y-auto hidden"></div>
                    <input type="hidden" name="predmet_id" id="selectedSubjectId" required>
                </div>

                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" id="cancelAddModal" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100 shadow-sm transition hover:scale-105">
                        Otkaži
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transform transition hover:scale-105">
                        Dodaj
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const addModal = document.getElementById('addSubjectModal');
        const addBtn = document.getElementById('addSubjectBtn');
        const cancelAdd = document.getElementById('cancelAddModal');

        if(addBtn) {
            addBtn.addEventListener('click', () => {
                addModal.classList.remove('hidden');
                addModal.classList.add('flex');
            });
        }

        if(cancelAdd) {
            cancelAdd.addEventListener('click', () => {
                addModal.classList.add('hidden');
                addModal.classList.remove('flex');
            });
        }

        // Close modal on outside click
        if(addModal) {
            addModal.addEventListener('click', (e) => {
                if (e.target === addModal) {
                    addModal.classList.add('hidden');
                    addModal.classList.remove('flex');
                }
            });
        }


        const searchInput = document.getElementById('searchSubject');
        const rows = document.querySelectorAll('.subject-row');

        if(searchInput) {
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase().trim();
                
                rows.forEach(row => {
                    const searchText = row.getAttribute('data-search');
                    if (searchText.includes(searchTerm)) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });
            });
        }

        const subjectSearchInput = document.getElementById('subjectSearchInput');
        const subjectSearchResults = document.getElementById('subjectSearchResults');
        const selectedSubjectId = document.getElementById('selectedSubjectId');
        const allSubjects = @json($predmeti);

        function filterAndShowSubjects(query) {
            // Filter logic: match name AND ensure not already assigned? 
            // For now just match name as per original code.
            const filtered = query === '' 
                ? allSubjects 
                : allSubjects.filter(s => s.naziv.toLowerCase().includes(query));

            subjectSearchResults.innerHTML = '';
            if (filtered.length > 0) {
                filtered.forEach(subject => {
                    const div = document.createElement('div');
                    div.className = 'px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-gray-700';
                    div.textContent = `${subject.naziv} (${subject.ects} ECTS)`;
                    div.onclick = () => {
                        subjectSearchInput.value = subject.naziv;
                        selectedSubjectId.value = subject.id;
                        subjectSearchResults.classList.add('hidden');
                    };
                    subjectSearchResults.appendChild(div);
                });
                subjectSearchResults.classList.remove('hidden');
            } else {
                subjectSearchResults.classList.add('hidden');
            }
        }

        if(subjectSearchInput) {
            subjectSearchInput.addEventListener('input', (e) => {
                filterAndShowSubjects(e.target.value.toLowerCase());
            });

            subjectSearchInput.addEventListener('focus', () => {
                filterAndShowSubjects(subjectSearchInput.value.toLowerCase());
            });

            document.addEventListener('click', (e) => {
                if (!subjectSearchInput.contains(e.target) && !subjectSearchResults.contains(e.target)) {
                    subjectSearchResults.classList.add('hidden');
                }
            });
        }
    });
    </script>
</x-app-layout>
