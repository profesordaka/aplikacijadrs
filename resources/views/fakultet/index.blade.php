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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Naziv</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Telefon</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Web</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Univerzitet</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Akcije</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($fakulteti as $f)
                        <tr class="faculty-row hover:bg-gray-50" data-search="{{ strtolower($f->naziv . ' ' . $f->email . ' ' . $f->univerzitet->naziv) }}">
                            <td class="px-6 py-4">{{ $f->naziv }}</td>
                            <td class="px-6 py-4">{{ $f->email }}</td>
                            <td class="px-6 py-4">{{ $f->telefon }}</td>
                            <td class="px-6 py-4">
                                @if($f->web)
                                    <a href="{{ $f->web }}" target="_blank" class="text-blue-600 hover:underline">{{ $f->web }}</a>
                                @endif
                            </td>
                            <td class="px-6 py-4">{{ $f->univerzitet->naziv }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center space-x-2">
                                    <button class="openEditModal text-indigo-600 bg-indigo-50 px-3 py-1 rounded-md"
                                        data-id="{{ $f->id }}"
                                        data-naziv="{{ $f->naziv }}"
                                        data-email="{{ $f->email }}"
                                        data-telefon="{{ $f->telefon }}"
                                        data-web="{{ $f->web }}"
                                        data-uputstvo="{{ $f->uputstvo_za_ocjene }}"
                                        data-univerzitet="{{ $f->univerzitet_id }}">
                                        Izmijeni
                                    </button>
                                    <a href="{{ route('fakulteti.predmeti.index', $f->id) }}"
   class="text-green-600 bg-green-50 px-3 py-1 rounded-md">
   Predmeti
</a>

                                    <form action="{{ route('fakulteti.destroy', $f->id) }}" method="POST" onsubmit="return confirm('Da li ste sigurni?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 bg-red-50 px-3 py-1 rounded-md">Obriši</button>
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
                    <input type="text" name="naziv" id="addName" class="w-full border rounded" required>
                </div>

                <div class="mb-4">
                    <label>Email</label>
                    <input type="email" name="email" id="addEmail" class="w-full border rounded" required>
                </div>

                <div class="mb-4">
                    <label>Telefon</label>
                    <input type="text" name="telefon" id="addPhone" class="w-full border rounded" required>
                </div>

                <div class="mb-4">
                    <label>Web</label>
                    <input type="text" name="web" id="addWeb" class="w-full border rounded">
                </div>

                <div class="mb-4">
                    <label>Univerzitet</label>
                    <select name="univerzitet_id" id="addUniversity" class="w-full border rounded" required>
                        <option value="">Izaberite</option>
                        @foreach($univerziteti as $u)
                            <option value="{{ $u->id }}">{{ $u->naziv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label>Uputstvo za ocjene</label>
                    <textarea name="uputstvo_za_ocjene" id="addInstructions" class="w-full border rounded"></textarea>
                </div>

                <div class="mb-4">
                    <label>Upload dokument</label>
                    <input type="file" name="uputstvo_file" class="w-full border rounded">
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
                    <label>Univerzitet</label>
                    <select id="editUniversity" name="univerzitet_id" class="w-full border rounded" required>
                        <option value="">Izaberite</option>
                        @foreach($univerziteti as $u)
                            <option value="{{ $u->id }}">{{ $u->naziv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label>Uputstvo za ocjene</label>
                    <textarea id="editInstructions" name="uputstvo_za_ocjene" class="w-full border rounded"></textarea>
                </div>

                <div class="mb-4">
                    <label>Upload dokument</label>
                    <input type="file" name="uputstvo_file" class="w-full border rounded">
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelEditModal" class="border px-4 py-2">Otkaži</button>
                    <button type="submit" class="bg-blue-600 text-white px-4 py-2">Sačuvaj</button>
                </div>
            </form>
        </div>
    </div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addModal = document.getElementById('addFacultyModal');
    const editModal = document.getElementById('editFacultyModal');

    document.getElementById('addFacultyBtn').onclick = () => { addModal.classList.remove('hidden'); addModal.classList.add('flex'); };
    document.getElementById('cancelAddModal').onclick = () => { addModal.classList.add('hidden'); addModal.classList.remove('flex'); };

                <div class="mb-4">
                    <label for="editEmail" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" id="editEmail" name="email" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label for="editPhone" class="block text-gray-700 font-medium mb-1">Telefon</label>
                    <input type="text" id="editPhone" name="telefon" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                </div>

                <div class="mb-4">
                    <label for="editWeb" class="block text-gray-700 font-medium mb-1">Web sajt</label>
                    <input type="text" id="editWeb" name="web" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="editUniversity" class="block text-gray-700 font-medium mb-1">Univerzitet</label>
                    <select id="editUniversity" name="univerzitet_id" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                        <option value="">Izaberite univerzitet</option>
                        @foreach($univerziteti as $u)
                            <option value="{{ $u->id }}">{{ $u->naziv }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-4">
                    <label for="editInstructions" class="block text-gray-700 font-medium mb-1">Uputstvo za ocjene</label>
                    <textarea id="editInstructions" name="uputstvo_za_ocjene" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelEditModal" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100 shadow-lg transform transition hover:scale-105">
                        Otkaži
                    </button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transform transition hover:scale-105">
                        Sačuvaj Izmjene
                    </button>
                </div>
            </form>
        </div>
    </div>

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

    // EDIT MODAL
    document.querySelectorAll('.openEditModal').forEach(btn => {
        btn.onclick = () => {
            document.getElementById('editFacultyId').value = btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.naziv;
            document.getElementById('editEmail').value = btn.dataset.email;
            document.getElementById('editPhone').value = btn.dataset.telefon;
            document.getElementById('editWeb').value = btn.dataset.web;
            document.getElementById('editInstructions').value = btn.dataset.uputstvo;
            document.getElementById('editUniversity').value = btn.dataset.univerzitet;

            // ✅ ISPRAVLJENA RUTA
            document.getElementById('editFacultyForm').action =
                `/admin/fakulteti/${btn.dataset.id}`;

            editModal.classList.remove('hidden');
            editModal.classList.add('flex');
        };
    });

    document.getElementById('cancelEditModal').onclick = () => {
        editModal.classList.add('hidden');
        editModal.classList.remove('flex');
    };

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
