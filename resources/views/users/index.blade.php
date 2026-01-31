<x-app-layout>

    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-800 p-3 rounded-md">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Greška!</strong>
            <span class="block">Došlo je do greške pri unosu:</span>
            <ul class="mt-2 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Korisnici</h1>
            <button id="addUserBtn" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                Dodaj korisnika
            </button>
        </div>

        <div class="mb-4">
            <input 
                type="text" 
                id="searchUser" 
                placeholder="Pretrazi.." 
                class="w-full max-w-md border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2"
            >
        </div>

        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Lista korisnika</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ count($users) }} Ukupno</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200" id="userTable">
                    <thead class="bg-gray-50">
                        <tr>

                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ime</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tip</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Radnje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200" id="userTableBody">
                        @foreach ($users as $user)
                            <tr class="user-row hover:bg-gray-50 transition-colors duration-150 ease-in-out" data-search="{{ strtolower($user->id . ' ' . $user->name . ' ' . $user->email . ' ' . (($user->type == 0) ? 'admin' : 'profesor')) }}">

                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if ((int)$user->type === 0)
                                    Admin
                                @elseif((int)$user->type === 1)
                                    Profesor
                                @endif
                           </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button
                                        onclick="openEditModal({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}')"
                                        class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors">
                                        Izmijeni
                                    </button>

                                    @if((int)$user->type === 1)
                                        <a href="{{ route('users.subjects.index', $user->id) }}"
                                           class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-md transition-colors inline-block">
                                            Pregled predmeta
                                        </a>
                                    @endif

                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                          onsubmit="return confirm('Jeste li sigurni da želite obrisati ovaj profil?')">
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
    </div>

    <div id="userModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-lg w-full max-w-md p-6 relative">
            <h2 id="modalTitle" class="text-xl font-semibold mb-4">Dodaj korisnika</h2>

            <form id="userForm" action="{{ route('users.store') }}" method="POST" >
                @csrf
                <input type="hidden" name="id" id="userId">

                <div class="mb-4">
                    <label for="name" class="block text-gray-700 font-medium mb-1">Ime</label>
                    <input type="text" id="name" name="name"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
                    <input type="email" id="email" name="email"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500"
                           required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-1">Šifra</label>
                    <input type="password" id="password" name="password"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-gray-700 font-medium mb-1">Potvrdi šifru</label>
                      <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-gray-700 font-medium mb-1">Tip korisnika</label>
                    <select name="type" class="border p-2 w-full">
                        <option value="0">Admin</option>
                        <option value="1">Profesor</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2">
                    <button type="button" id="cancelUserModal"
                            class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100 shadow-lg transform transition hover:scale-105">
                        Otkaži
                    </button>
                    <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transform transition hover:scale-105">
                        Sačuvaj
                    </button>
                </div>
            </form>
        </div>
    </div>



    <script>
        const modal = document.getElementById('userModal');
        const addUserBtn = document.getElementById('addUserBtn');
        const cancelModal = document.getElementById('cancelUserModal');
        const form = document.getElementById('userForm');
        const title = document.getElementById('modalTitle');

        addUserBtn.addEventListener('click', () => {
            form.action = "{{ route('users.store') }}";
            form.reset();
            document.getElementById('password').required = true;
            title.textContent = 'Dodaj korisnika';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        });

        cancelModal.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

    function openEditModal(id, name, email) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        title.textContent = 'Izmijeni korisnika';
        form.action = `/admin/users/${id}`;
        form.method = 'POST';

        const existingMethod = form.querySelector('input[name="_method"]');
        if (existingMethod) existingMethod.remove();

        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'PUT';
        form.appendChild(methodInput);

        document.getElementById('userId').value = id;
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;
        document.getElementById('password').required = false;
    }

    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('searchUser');
        const rows = document.querySelectorAll('.user-row');

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
