<x-app-layout>
    @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 mb-4 rounded">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 text-red-700 p-3 mb-4 rounded">
            {{ session('error') }}
        </div>
    @endif

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Univerziteti</h1>
            <button id="addUniversityBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                Dodaj Univerzitet
            </button>
        </div>

        <div class="mb-4">
            <input 
                type="text" 
                id="searchUniversity" 
                placeholder="Pretrazi.." 
                class="w-full max-w-md border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
            >
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Spisak Univerziteta</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ count($univerziteti) }} Ukupno</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Naziv</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Država</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Grad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Radnja</th>
                        </tr>
                    </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($univerziteti as $u)
                        <tr class="university-row hover:bg-gray-50 transition-colors duration-150 ease-in-out" data-search="{{ strtolower($u->naziv . ' ' . $u->drzava . ' ' . $u->grad . ' ' . $u->email) }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $u->naziv }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $u->drzava }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $u->grad }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $u->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                <div class="flex justify-center space-x-2">
                                   <button
    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors openEditModal"
    data-id="{{ $u->id }}"
    data-naziv="{{ $u->naziv }}"
    data-drzava="{{ $u->drzava }}"
    data-grad="{{ $u->grad }}"
    data-email="{{ $u->email }}">
    Izmijeni
</button>
                                    <form action="{{ route('univerzitet.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Jeste li sigurni?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors">
                                            Izbriši
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>

<div id="editUniversityModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h2 id="modalTitle" class="text-xl font-semibold mb-4">Promijeni Univerzitet</h2>

        <form id="editUniversityForm" method="POST">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="id" id="editUniversityId">

            <div class="mb-4">
                <label for="editName" class="block text-gray-700 font-medium mb-1">Ime Univerziteta</label>
                <input type="text" id="editName" name="naziv" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

                <div class="mb-4">
                    <label class="block mb-1 font-medium">Country</label>
                    <select id="editCountry" name="drzava" class="border rounded px-3 py-2 w-full" required>
                        <option value="">Izaberi zemlju</option>
                        @foreach($countries as $country)
                            <option value="{{ $country }}" {{ old('drzava') === $country ? 'selected' : '' }}>
                                {{ $country }}
                            </option>
                        @endforeach
                    </select>
                    @error('drzava')<div class="text-red-600 mt-1">{{ $message }}</div>@enderror
                </div>


            <div class="mb-4">
                <label for="editCity" class="block text-gray-700 font-medium mb-1">Grad</label>
                <input type="text" id="editCity" name="grad" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label for="editEmail" class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" id="editEmail" name="email" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelEditModal" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100 shadow-lg transform transition hover:scale-105">
                    Otkaži
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transform transition hover:scale-105">
                    Sačuvaj
                </button>
            </div>
        </form>
    </div>
</div>

<div id="addUniversityModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
        <h2 id="modalTitleAdd" class="text-xl font-semibold mb-4">Dodaj Univerzitet</h2>

        <form id="addUniversityForm" action="{{ route('univerzitet.store') }}" method="POST">
            @csrf

            <div class="mb-4">
                <label for="addName" class="block text-gray-700 font-medium mb-1">Ime Univerziteta</label>
                <input type="text" id="addName" name="naziv" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label for="addCountry" class="block text-gray-700 font-medium mb-1">Država</label>
                <select id="addCountry" name="drzava" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                    <option value="">Izaberi državu</option>
                    @foreach($countries as $country)
                        <option value="{{ $country }}">{{ $country }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="addCity" class="block text-gray-700 font-medium mb-1">Grad</label>
                <input type="text" id="addCity" name="grad" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div class="mb-4">
                <label for="addEmail" class="block text-gray-700 font-medium mb-1">Email</label>
                <input type="email" id="addEmail" name="email" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
            </div>

            <div class="flex justify-end space-x-2">
                <button type="button" id="cancelAddModal" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100 shadow-lg transform transition hover:scale-105">
                    Otkaži
                </button>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transform transition hover:scale-105">
                    Dodaj
                </button>
            </div>
        </form>
    </div>
</div>


        </div>
    </div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.getElementById('editUniversityModal');
        const cancelBtn = document.getElementById('cancelEditModal');
        const form = document.getElementById('editUniversityForm');

        document.querySelectorAll('.openEditModal').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.getAttribute('data-id');
                document.getElementById('editUniversityId').value = id;
                document.getElementById('editName').value = button.getAttribute('data-naziv');
                document.getElementById('editCountry').value = button.getAttribute('data-drzava');
                document.getElementById('editCity').value = button.getAttribute('data-grad');
                document.getElementById('editEmail').value = button.getAttribute('data-email');

                form.action = `{{ route('univerzitet.index') }}/${id}`;
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            });
        });

        cancelBtn.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const addModal = document.getElementById('addUniversityModal');
        const addBtn = document.getElementById('addUniversityBtn');
        const cancelAdd = document.getElementById('cancelAddModal');
        const addForm = document.getElementById('addUniversityForm');

        addBtn.addEventListener('click', () => {
            addForm.reset();
            addModal.classList.remove('hidden');
            addModal.classList.add('flex');
        });

        cancelAdd.addEventListener('click', () => {
            addModal.classList.add('hidden');
            addModal.classList.remove('flex');
            addForm.reset();
        });
    });

    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchUniversity');
        const rows = document.querySelectorAll('.university-row');

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
    });


</script>


</x-app-layout>
