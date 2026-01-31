<x-app-layout>

  @if(session('success'))
    <div class="mb-4 bg-green-100 text-green-800 p-3 rounded-md">
      {{ session('success') }}
    </div>
  @endif

  @if ($errors->any())
    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
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
      <h1 class="text-3xl font-bold text-gray-900">Studenti</h1>
      <a href="{{ route('students.create') }}"
        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
        Dodaj studenta
      </a>
    </div>

    <div class="mb-4 flex flex-col md:flex-row gap-4">
      <input type="text" id="searchStudent" placeholder="Pretraži.."
        class="w-full max-w-md border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
      
      <form method="GET" action="{{ route('students.index') }}" class="w-full max-w-xs">
          <select name="status" onchange="this.form.submit()" class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500 px-4 py-2">
              <option value="">Svi statusi</option>
              <option value="mobilnost" {{ request('status') == 'mobilnost' ? 'selected' : '' }}>Mobilnost</option>
              <option value="prepis" {{ request('status') == 'prepis' ? 'selected' : '' }}>Prepis</option>
          </select>
      </form>
    </div>

    <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
      <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-gray-800">Lista studenata</h2>
        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ count($students) }}
          Ukupno</span>
      </div>

      @if($students->count() > 0)
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200" id="studentTable">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ime i Prezime
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Indeks</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
               <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Godina</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fakultet</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nivo</th>
              <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Radnja</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200" id="studentTableBody">
            @foreach ($students as $student)
              <tr class="student-row hover:bg-gray-50 transition-colors duration-150 ease-in-out"
                data-search="{{ strtolower($student->ime . ' ' . $student->prezime . ' ' . $student->br_indexa . ' ' . $student->email) }}">

                <td class="px-6 py-4">
                  <div class="flex items-center">
                    <div
                      class="flex-shrink-0 h-10 w-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-bold">
                      {{ substr($student->ime, 0, 1) }}{{ substr($student->prezime, 0, 1) }}
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ $student->ime }} {{ $student->prezime }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->br_indexa }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">{{ $student->email }}</td>
                 <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->godina_studija }}</td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ $student->fakulteti->first()->naziv ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $student->nivoStudija->naziv ?? 'N/A' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <a href="{{ route('students.edit', $student->id) }}"
                      class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors">
                      Izmijeni
                    </a>

                    <form action="{{ route('students.destroy', $student->id) }}" method="POST"
                      onsubmit="return confirm('Jeste li sigurni da želite da obrišete studenta?')">
                      @csrf
                      @method('DELETE')
                      <button type="submit"
                        class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-md transition-colors">
                        Obriši
                      </button>
                    </form>
                  </div>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>

      </div>
      @else
      <div class="p-6 text-center text-gray-500">
          Nema studenata za prikaz.
      </div>
      @endif
    </div>
  </div>

  <div id="studentModal" class="fixed inset-0 bg-gray-800 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative max-h-[90vh] overflow-y-auto">
      <h2 id="modalTitle" class="text-xl font-semibold mb-4">Dodaj studenta</h2>

      <form id="studentForm" action="{{ route('students.store') }}" method="POST">
        @csrf
        <input type="hidden" name="id" id="studentId">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="mb-4">
            <label for="ime" class="block text-gray-700 font-medium mb-1">Ime</label>
            <input type="text" id="ime" name="ime"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="prezime" class="block text-gray-700 font-medium mb-1">Prezime</label>
            <input type="text" id="prezime" name="prezime"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="br_indexa" class="block text-gray-700 font-medium mb-1">Broj Indeksa</label>
            <input type="text" id="br_indexa" name="br_indexa"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="datum_rodjenja" class="block text-gray-700 font-medium mb-1">Datum Rodjenja</label>
            <input type="date" id="datum_rodjenja" name="datum_rodjenja"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="telefon" class="block text-gray-700 font-medium mb-1">Telefon</label>
            <input type="text" id="telefon" name="telefon"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="email" class="block text-gray-700 font-medium mb-1">Email</label>
            <input type="email" id="email" name="email"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="godina_studija" class="block text-gray-700 font-medium mb-1">Godina Studija</label>
            <input type="number" id="godina_studija" name="godina_studija"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label for="jmbg" class="block text-gray-700 font-medium mb-1">JMBG</label>
            <input type="text" id="jmbg" name="jmbg"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
          </div>

          <div class="mb-4">
            <label class="block text-gray-700 font-medium mb-1">Pol</label>
            <div class="flex items-center space-x-6">
              <label class="inline-flex items-center">
                <input type="radio" name="pol" id="pol_muski" value="musko" checked
                  class="form-radio text-blue-600" />
                <span class="ml-2 text-gray-700">Muški</span>
              </label>

              <label class="inline-flex items-center">
                <input type="radio" name="pol" id="pol_zenski" value="zensko"
                  class="form-radio text-blue-600" />
                <span class="ml-2 text-gray-700">Ženski</span>
              </label>
            </div>
          </div>

           <div class="mb-4">
            <label for="nivo_studija_id" class="block text-gray-700 font-medium mb-1">Nivo Studija</label>
            <select id="nivo_studija_id" name="nivo_studija_id"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
              <option value="">Odaberite nivo studija</option>
              @foreach($nivoStudija as $nivo)
                <option value="{{ $nivo->id }}">{{ $nivo->naziv }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-4">
            <label for="fakultet_id" class="block text-gray-700 font-medium mb-1">Fakultet</label>
            <select id="fakultet_id" name="fakultet_id"
              class="w-full border-gray-300 rounded-lg shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
              <option value="">Odaberite fakultet</option>
              @foreach($fakulteti as $fakultet)
                <option value="{{ $fakultet->id }}">{{ $fakultet->naziv }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="flex justify-end space-x-2 mt-4">
          <button type="button" id="cancelModal" class="px-4 py-2 rounded-md border border-gray-300 hover:bg-gray-100 shadow-lg transform transition hover:scale-105">
            Cancel
          </button>
          <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow-lg transform transition hover:scale-105">
            Save
          </button>
        </div>
      </form>
    </div>
  </div>

  <script>
    const modal = document.getElementById('studentModal');
    const addStudentBtn = document.getElementById('addStudentBtn');
    const cancelModal = document.getElementById('cancelModal');
    const form = document.getElementById('studentForm');
    const title = document.getElementById('modalTitle');

    addStudentBtn.addEventListener('click', () => {
      form.action = "{{ route('students.store') }}";
      form.reset();

      // Remove method spoofing if it exists
      const existingMethod = form.querySelector('input[name="_method"]');
      if (existingMethod) existingMethod.remove();

      title.textContent = 'Add Student';
      modal.classList.remove('hidden');
      modal.classList.add('flex');
    });

    cancelModal.addEventListener('click', () => {
      modal.classList.add('hidden');
      modal.classList.remove('flex');
    });

    function openEditModal(student) {
      modal.classList.remove('hidden');
      modal.classList.add('flex');
      title.textContent = 'Edit Student';
      form.action = `/admin/students/${student.id}`;

      // Add method spoofing for PUT
      const existingMethod = form.querySelector('input[name="_method"]');
      if (existingMethod) existingMethod.remove();

      const methodInput = document.createElement('input');
      methodInput.type = 'hidden';
      methodInput.name = '_method';
      methodInput.value = 'PUT';
      form.appendChild(methodInput);

      document.getElementById('studentId').value = student.id;
      document.getElementById('ime').value = student.ime;
      document.getElementById('prezime').value = student.prezime;
      document.getElementById('br_indexa').value = student.br_indexa;
      document.getElementById('datum_rodjenja').value = student.datum_rodjenja;
      document.getElementById('telefon').value = student.telefon;
      document.getElementById('email').value = student.email;
      document.getElementById('godina_studija').value = student.godina_studija;
      document.getElementById('jmbg').value = student.jmbg;
      document.getElementById('nivo_studija_id').value = student.nivo_studija_id;
      
      if (student.fakulteti && student.fakulteti.length > 0) {
        document.getElementById('fakultet_id').value = student.fakulteti[0].id;
      } else {
        document.getElementById('fakultet_id').value = '';
      }

      // Set pol radio (student.pol may be 0/1 or boolean)
      const polMuski = document.getElementById('pol_muski');
      const polZenski = document.getElementById('pol_zenski');
      if (student.pol === 'musko') {
        polMuski.checked = true;
      } else {
        polZenski.checked = true;
      }
    }
  </script>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('searchStudent');
      const rows = document.querySelectorAll('.student-row');

      searchInput.addEventListener('input', function () {
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