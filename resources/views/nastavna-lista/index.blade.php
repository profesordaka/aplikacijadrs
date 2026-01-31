<x-app-layout>
    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Nastavne liste</h1>
                <p class="text-gray-600 mt-2">Predmet: <span class="font-semibold text-blue-600">{{ $predmet->naziv }}</span> ({{ $predmet->sifra_predmeta }})</p>
                <p class="text-gray-500 text-sm">Fakultet: {{ $predmet->fakultet->naziv }}</p>
            </div>
            <a href="{{ route('fakulteti.predmeti.index', $predmet->fakultet_id) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Nazad
            </a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <p>{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm" role="alert">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 @if(auth()->user()->type == 0) lg:grid-cols-3 @endif gap-8">
            <!-- Form Card -->
            @if(auth()->user()->type == 0)
            <div class="lg:col-span-1">
                <div class="bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100">
                    <div class="p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Dodaj novu verziju
                        </h2>
                        
                        <form action="{{ route('nastavne-liste.store', $predmet->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Studijska godina</label>
                                    <input type="text" name="studijska_godina" placeholder="npr. 2024/25" required
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition duration-200">
                                </div>

                                <div class="relative">
                                    <div class="absolute inset-x-0 top-0 flex items-center" aria-hidden="true">
                                        <div class="w-full border-t border-gray-300"></div>
                                    </div>
                                    <div class="relative flex justify-center text-sm">
                                        <span class="px-2 bg-white text-gray-500 italic">Unesite link ili učitajte fajl</span>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">External Link (URL)</label>
                                    <input type="url" name="link" placeholder="https://example.com/syllabus"
                                           class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500 shadow-sm transition duration-200">
                                </div>

                                <div class="pt-2">
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Upload Syllabus (PDF/Doc)</label>
                                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-lg hover:border-blue-400 transition-colors duration-200 cursor-pointer" onclick="document.getElementById('fileInput').click()">
                                        <div class="space-y-1 text-center">
                                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <div class="flex text-sm text-gray-600">
                                                <span class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                                    Izaberi fajl
                                                </span>
                                            </div>
                                            <p class="text-xs text-gray-500">PDF, DOC, DOCX do 10MB</p>
                                        </div>
                                        <input id="fileInput" name="file" type="file" class="sr-only">
                                    </div>
                                    <p id="fileNameDisplay" class="text-sm text-gray-500 mt-2 italic hidden"></p>
                                </div>

                                <button type="submit" 
                                        class="w-full inline-flex justify-center items-center px-4 py-3 bg-blue-600 border border-transparent rounded-lg font-semibold text-white  tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200 shadow-md hover:shadow-lg">
                                    Sačuvaj nastavnu listu
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            <!-- List Card -->
            <div class="@if(auth()->user()->type == 0) lg:col-span-2 @else col-span-full @endif">
                <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
                    {{-- <div class="p-6 border-b border-gray-100">
                        <h2 class="text-xl font-bold text-gray-800">Liste</h2>
                    </div> --}}
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-gray-600 font-medium text-sm">
                                <tr>
                                    <th class="px-6 py-3 border-b border-gray-100">Studijska godina</th>
                                    <th class="px-6 py-3 border-b border-gray-100">Tip</th>
                                    <th class="px-6 py-3 border-b border-gray-100 text-right">Akcije</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($nastavneListe as $lista)
                                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                                        <td class="px-6 py-4 font-semibold text-gray-700">{{ $lista->studijska_godina }}</td>
                                        <td class="px-6 py-4">
                                            @if($lista->link)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                                    </svg>
                                                    Link
                                                </span>
                                            @endif
                                            @if($lista->file_path)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800 @if($lista->link) ml-2 @endif">
                                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                                    </svg>
                                                    Fajl
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right space-x-2">
                                            @if($lista->link)
                                                <a href="{{ $lista->link }}" target="_blank" 
                                                   class="inline-flex items-center text-indigo-600 hover:text-indigo-900 font-medium transition duration-200" title="Otvori link">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                                    </svg>
                                                </a>
                                            @endif
                                            @if($lista->file_path)
                                                <a href="{{ route('nastavne-liste.download', $lista->id) }}"
                                                   class="inline-flex items-center text-emerald-600 hover:text-emerald-900 font-medium transition duration-200" title="Preuzmi fajl">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                                    </svg>
                                                </a>
                                            @endif
                                            @if(auth()->user()->type == 0)
                                            <form action="{{ route('nastavne-liste.destroy', $lista->id) }}" method="POST" class="inline" onsubmit="return confirm('Jeste li sigurni?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="inline-flex items-center text-red-600 hover:text-red-900 transition duration-200" title="Obriši">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                    </svg>
                                                </button>
                                            </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-6 py-12 text-center text-gray-500 italic">
                                            Nema dodatih nastavnih lista za ovaj predmet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('fileInput')?.addEventListener('change', function(e) {
            const fileName = e.target.files[0]?.name;
            const display = document.getElementById('fileNameDisplay');
            if (fileName) {
                display.textContent = 'Izabran fajl: ' + fileName;
                display.classList.remove('hidden');
            } else {
                display.classList.add('hidden');
            }
        });
    </script>
</x-app-layout>
