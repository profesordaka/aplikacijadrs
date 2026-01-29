<x-app-layout>
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Fakulteti</h1>
            <button id="addFacultyBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                Dodaj Fakultet
            </button>
        </div>

        <div class="mb-4">
            <input type="text" id="searchFaculty" placeholder="Pretraži.." class="w-full max-w-md border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Lista Fakulteta</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ count($fakulteti) }} Total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Naziv</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Univerzitet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokument</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Akcije</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($fakulteti as $f)
                        <tr class="faculty-row hover:bg-gray-50 transition-colors duration-150 ease-in-out" data-search="{{ strtolower($f->naziv . ' ' . $f->email . ' ' . $f->univerzitet->naziv) }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                        {{ substr($f->naziv, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $f->naziv }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $f->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $f->telefon }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $f->univerzitet->naziv }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                @if($f->uputstvo_file)
                                    <a href="{{ route('fakulteti.view-pdf', $f->id) }}" 
                                       target="_blank"
                                       rel="noopener noreferrer"
                                       class="text-white bg-blue-600 whitespace-nowrap hover:bg-blue-700 px-3 py-2 rounded text-xs font-medium transition-colors">
                                       Otvori PDF
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex justify-center space-x-2">
                                    <button class="openEditModal text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors"
                                        data-id="{{ $f->id }}"
                                        data-naziv="{{ $f->naziv }}"
                                        data-email="{{ $f->email }}"
                                        data-telefon="{{ $f->telefon }}"
                                        data-web="{{ $f->web }}"

                                        data-univerzitet="{{ $f->univerzitet_id }}"
                                        data-file-path="{{ $f->file_path ? route('fakulteti.download', $f->id) : '' }}">
                                        Izmijeni
                                    </button>
                                    <a href="{{ route('fakulteti.predmeti.index', $f->id) }}"
                                       class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md transition-colors">
                                       Predmeti
                                    </a>
                                    <form action="{{ route('fakulteti.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Jeste li sigurni?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors">Obriši</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ADD MODAL -->
    <div id="addFacultyModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 overflow-y-auto max-h-screen">
            <h2 class="text-xl font-semibold mb-4">Dodaj Fakultet</h2>
            <form action="{{ route('fakulteti.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-4">
                    <label>Naziv</label>
                    <input type="text" name="naziv" class="w-full border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Email</label>
                    <input type="email" name="email" class="w-full border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Telefon</label>
                    <input type="text" name="telefon" class="w-full border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Web</label>
                    <input type="text" name="web" class="w-full border rounded">
                </div>

<div class="mb-4">
    <label class="block text-gray-700 font-medium mb-1">Univerzitet (opciono)</label>

    <!-- Select za postojeće univerzitete -->
    <select id="addUniversity" name="univerzitet_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 mb-2">
        <option value="">Izaberite univerzitet</option>
        @foreach($univerziteti as $u)
            <option value="{{ $u->id }}">{{ $u->naziv }}</option>
        @endforeach
    </select>

    <!-- Polje za dodavanje novog univerziteta -->
    <input type="text" id="newUniversity" name="new_univerzitet" placeholder="Dodajte novi univerzitet ako nije u listi"
           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
    <p class="text-gray-400 text-sm mt-1">Ako unesete novi univerzitet, on će biti kreiran i povezan sa fakultetom.</p>
</div>




                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelAddModal" class="border px-4 py-2">Otkaži</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2">Sačuvaj</button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT MODAL -->
    <div id="editFacultyModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 overflow-y-auto max-h-screen">
            <h2 class="text-xl font-semibold mb-4">Izmijeni Fakultet</h2>

            <form id="editFacultyForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <input type="hidden" id="editFacultyId" name="id">
                <div class="mb-4">
                    <label>Naziv</label>
                    <input type="text" id="editName" name="naziv" class="w-full border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Email</label>
                    <input type="email" id="editEmail" name="email" class="w-full border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Telefon</label>
                    <input type="text" id="editPhone" name="telefon" class="w-full border rounded" required>
                </div>
                <div class="mb-4">
                    <label>Web</label>
                    <input type="text" id="editWeb" name="web" class="w-full border rounded">
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Univerzitet (opciono)</label>

                    <!-- Select za postojeće univerzitete -->
                    <select id="editUniversity" name="univerzitet_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 mb-2">
                        <option value="">Izaberite univerzitet</option>
                        @foreach($univerziteti as $u)
                            <option value="{{ $u->id }}">{{ $u->naziv }}</option>
                        @endforeach
                    </select>

                    <!-- Polje za dodavanje novog univerziteta -->
                    <input type="text" id="editNewUniversity" name="new_univerzitet" placeholder="Dodajte novi univerzitet ako nije u listi"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-gray-400 text-sm mt-1">Ako unesete novi univerzitet, on će biti kreiran i povezan sa fakultetom.</p>
                </div>




                <div class="mb-4">
                    <label for="editFile" class="block text-gray-700 font-medium mb-1">Uputstvo za ocjene</label>
                    <div id="currentFileContainer" class="hidden mb-2">
                        <span class="text-sm text-gray-600">Trenutni fajl: </span>
                        <a id="currentFileLink" href="#" class="text-blue-600 hover:underline text-sm font-medium">Preuzmi</a>
                    </div>
                    <input type="file" id="editFile" name="file" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Opciono. Upload-ujte fajl sa uputstvom za ocjenjivanje.</p>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelEditModal" class="border px-4 py-2">Otkaži</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2">Sačuvaj Izmjene</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT -->
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const addModal = document.getElementById('addFacultyModal');
        const editModal = document.getElementById('editFacultyModal');

        // ADD MODAL
        document.getElementById('addFacultyBtn').onclick = () => {
            addModal.classList.remove('hidden');
            addModal.classList.add('flex');
        };
        document.getElementById('cancelAddModal').onclick = () => {
            addModal.classList.add('hidden');
            addModal.classList.remove('flex');
        };

        // Edit Modal Logic
const editModal = document.getElementById('editFacultyModal');
const cancelEdit = document.getElementById('cancelEditModal');
const editForm = document.getElementById('editFacultyForm');

document.querySelectorAll('.openEditModal').forEach(button => {
    button.addEventListener('click', () => {
        const id = button.getAttribute('data-id');
        document.getElementById('editFacultyId').value = id;
        document.getElementById('editName').value = button.getAttribute('data-naziv');
        document.getElementById('editEmail').value = button.getAttribute('data-email');
        document.getElementById('editPhone').value = button.getAttribute('data-telefon');
        document.getElementById('editWeb').value = button.getAttribute('data-web');

        document.getElementById('editUniversity').value = button.getAttribute('data-univerzitet');

        const filePath = button.getAttribute('data-file-path');
        const fileContainer = document.getElementById('currentFileContainer');
        const fileLink = document.getElementById('currentFileLink');

        if (filePath) {
            fileContainer.classList.remove('hidden');
            fileLink.href = filePath;
        } else {
            fileContainer.classList.add('hidden');
            fileLink.href = '#';
        }

        editForm.action = `{{ route('fakulteti.index') }}/${id}`;
        editModal.classList.remove('hidden');
        editModal.classList.add('flex');
    });
});

cancelEdit.addEventListener('click', () => {
    editModal.classList.add('hidden');
    editModal.classList.remove('flex');
});


        // SEARCH
        const searchInput = document.getElementById('searchFaculty');
        const rows = document.querySelectorAll('.faculty-row');
        searchInput.addEventListener('input', function () {
            const term = this.value.toLowerCase();
            rows.forEach(r => {
                r.style.display = r.dataset.search.includes(term) ? '' : 'none';
            });
        });
    });


    </script>
</x-app-layout>

