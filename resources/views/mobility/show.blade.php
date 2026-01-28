<x-app-layout>
    <style>
        .pdf-preview-tooltip {
            position: fixed !important;
            max-height: 80vh;
            z-index: 9999 !important;
        }
        @media (max-width: 768px) {
            .pdf-preview-tooltip {
                width: 90vw !important;
                max-width: 90vw;
                left: 50% !important;
                transform: translateX(-50%);
            }
        }
        @media (max-width: 640px) {
            .pdf-preview-tooltip {
                width: 95vw !important;
                max-width: 95vw;
                height: 70vh !important;
            }
        }
    </style>
    <div class="py-10 max-w-6xl mx-auto px-6">
        
        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg relative">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg relative">
                {{ session('error') }}
            </div>
        @endif

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Detalji o Mobilnosti</h1>
            <div class="flex gap-2">
            
                <button onclick="openDocumentsModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                    Pregled Dokumenata
                </button>
                <a href="{{ route('adminDashboardShow') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                    Nazad na Kontrolnu Tablu
                </a>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h2 class="text-lg font-semibold text-gray-700">Informacije o studentu</h2>
                    <p class="mt-2 text-gray-600"><span class="font-medium">Ime:</span> {{ $mobilnost->student->ime }} {{ $mobilnost->student->prezime }}</p>
                    <p class="text-gray-600"><span class="font-medium">Indeks:</span> {{ $mobilnost->student->br_indexa }}</p>
                </div>
                <div>
                    <h2 class="text-lg font-semibold text-gray-700">Informacije o mobilnosti</h2>
                    <p class="mt-2 text-gray-600"><span class="font-medium">Fakultet:</span> {{ $mobilnost->fakultet->naziv }}</p>
                    <p class="text-gray-600"><span class="font-medium">Period:</span> {{ \Carbon\Carbon::parse($mobilnost->datum_pocetka)->format('d.m.Y') }} - {{ \Carbon\Carbon::parse($mobilnost->datum_kraja)->format('d.m.Y') }}</p>
                    @if($mobilnost->fakultet->image_url)
                        <div class="mt-4">
                            <img src="{{ $mobilnost->fakultet->image_url }}" alt="Fakultet Slika" class="w-32 h-32 object-cover rounded-md shadow-lg">
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg overflow-hidden relative">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-800">Learning Agreements</h2>
            </div>
            <div class="overflow-x-auto">
                <form id="gradesForm">
                    <table class="min-w-full divide-y divide-gray-200 table-auto">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">FIT Predmeti</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Strani Predmeti</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ECTS</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    <div class="flex items-center gap-2 relative">
                                        <span>Ocjena</span>
                                        @if($mobilnost->fakultet->uputstvo_file)
                                            <div class="relative inline-block" title="Hover za preview PDF-a">
<a
    id="pdfTrigger-{{ $mobilnost->fakultet->id }}"
    href="{{ route('fakulteti.view-pdf', $mobilnost->fakultet->id) }}"
    target="_blank"
    rel="noopener noreferrer"
    class="inline-flex items-center justify-center cursor-pointer text-blue-600 hover:text-blue-800 transition-colors"
    title="Hover: pregled PDF-a | Klik: otvori u novoj kartici"
    aria-label="Pregledaj PDF uputstvo"
>
    <svg xmlns="http://www.w3.org/2000/svg"
         viewBox="0 0 24 24"
         class="w-6 h-6">
        <!-- plavi krug -->
        <circle cx="12" cy="12" r="10" fill="currentColor"/>
        <!-- bijeli znak uzvika -->
        <rect x="11" y="6" width="2" height="8" rx="1" fill="white"/>
        <circle cx="12" cy="17" r="1.2" fill="white"/>
    </svg>
</a>
                                            </div>
                                        @elseif($mobilnost->fakultet->uputstvo_za_ocjene)
                                            <span class="text-red-600 font-bold text-lg" title="{{ $mobilnost->fakultet->uputstvo_za_ocjene }}">!</span>
                                        @endif
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($mobilnost->learningAgreements as $la)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $la->fitPredmet ? $la->fitPredmet->naziv : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $la->straniPredmet ? $la->straniPredmet->naziv : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $la->straniPredmet ? $la->straniPredmet->ects : '-' }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <select name="grades[{{ $la->id }}]" {{ $mobilnost->is_locked ? 'disabled' : '' }}
                                                class="w-24 border-gray-300 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm {{ $mobilnost->is_locked ? 'bg-gray-100 cursor-not-allowed' : '' }}">
                                            <option value="">-</option>
                                            @foreach(['A', 'B', 'C', 'D', 'E', 'F'] as $grade)
                                                <option value="{{ $grade }}" {{ $la->ocjena == $grade ? 'selected' : '' }}>
                                                    {{ $grade }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex justify-end items-center gap-4">
                        <span id="saveMessage" class="text-sm font-medium"></span>
                        @if(!$mobilnost->is_locked)
                            <button type="button" onclick="openDisableModal()" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                                Onemogući Unos
                            </button>

                            <button type="button" onclick="saveAllGrades()" 
                                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow-lg transform transition hover:scale-105 duration-150 ease-in-out">
                                Sačuvaj Sve Ocjene
                            </button>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- PDF Preview Tooltip - izvan tabele -->
    @if($mobilnost->fakultet && $mobilnost->fakultet->uputstvo_file)
    <div id="pdfTooltip-{{ $mobilnost->fakultet->id }}" class="pdf-preview-tooltip w-[500px] h-[600px] bg-white border-2 border-gray-300 rounded-lg shadow-2xl opacity-0 invisible transition-all duration-200 overflow-hidden" style="display: none;">
        <div class="bg-gray-100 px-3 py-2 border-b border-gray-300 flex justify-between items-center">
            <span class="text-sm font-semibold text-gray-700 truncate">{{ $mobilnost->fakultet->naziv }} - PDF Uputstvo</span>
            <a href="{{ route('fakulteti.view-pdf', $mobilnost->fakultet->id) }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs underline whitespace-nowrap ml-2">Otvori u novom prozoru</a>
        </div>
        <div id="pdfPagesContainer-{{ $mobilnost->fakultet->id }}" class="w-full h-[calc(100%-45px)] overflow-y-auto bg-gray-50 p-4" style="display: none;">
            {{-- Koristi view-pdf rutu koja servuje PDF sa inline disposition --}}
            <iframe src="{{ route('fakulteti.view-pdf', $mobilnost->fakultet->id) }}" 
                    class="w-full h-full border-0"
                    frameborder="0"
                    title="PDF Preview">
            </iframe>
        </div>
        <script>
        (function() {
            const fakultetId = {{ $mobilnost->fakultet->id }};
            const container = document.getElementById('pdfPagesContainer-' + fakultetId);
            const tooltip = document.getElementById('pdfTooltip-' + fakultetId);
            const trigger = document.getElementById('pdfTrigger-' + fakultetId);
            
            if (trigger && tooltip && container) {
                trigger.addEventListener('mouseenter', function() {
                    container.style.display = 'block';
                });
                
                tooltip.addEventListener('mouseleave', function() {
                    container.style.display = 'none';
                });
            }
        })();
        </script>
    </div>
    @endif

    <!-- Disable Input Modal -->
    <div id="disableModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-1/3">
            <div class="px-6 py-4 border-b">
                <h3 class="text-lg font-semibold text-gray-800">Potvrdi onemoućavanje unosa</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600">Da li ste sigurni da želite da onemogućite sav unos? Ova radnja je trajna i ne može se opozvati.</p>
            </div>
            <div class="px-6 py-4 border-t flex justify-end gap-2">
                <button onclick="closeDisableModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105 duration-150 ease-in-out">Otkazi</button>
                <form action="{{ route('admin.mobility.lock', $mobilnost->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105 duration-150 ease-in-out">Onemogući za vazda</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Documents Modal -->
    <div id="documentsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 hidden">
        <div class="bg-white rounded-lg shadow-lg w-1/2">
            <div class="px-6 py-4 border-b flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-800">Dokumenti za mobilnost</h3>
                <button onclick="closeDocumentsModal()" class="text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <div class="p-6">
                <!-- Documents List -->
                <div id="documentsList" class="mb-6 space-y-2">
                    <p class="text-gray-500 text-sm">Loading...</p>
                </div>

                <!-- Add New Document -->
                @if(!$mobilnost->is_locked)
                <div class="border-t pt-4">
                    <h4 class="text-md font-semibold text-gray-700 mb-2">Dodaj novi dokument</h4>
                    <div class="flex gap-2">
                        <input type="file" id="newDocInput" class="border border-gray-300 rounded p-1 w-full text-sm">
                        <button onclick="uploadDocument()" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-3 py-1 rounded shadow text-sm">
                            Dodaj
                        </button>
                    </div>
                    <p id="uploadStatus" class="text-xs mt-1"></p>
                </div>
                @endif
            </div>
            <div class="px-6 py-4 border-t flex justify-between items-center">
                 <a href="{{ route('admin.mobility.documents.zip', $mobilnost->id) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                    Export (ZIP)
                </a>
                <button onclick="closeDocumentsModal()" class="bg-gray-500 hover:bg-gray-600 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                    Zatvori
                </button>
            </div>
        </div>
    </div>

    <script>
        // PDF Tooltip positioning
        document.addEventListener('DOMContentLoaded', function() {
            @if($mobilnost->fakultet && $mobilnost->fakultet->uputstvo_file)
            const trigger = document.getElementById('pdfTrigger-{{ $mobilnost->fakultet->id }}');
            const tooltip = document.getElementById('pdfTooltip-{{ $mobilnost->fakultet->id }}');
            
            if (trigger && tooltip) {
                function positionTooltip() {
                    const rect = trigger.getBoundingClientRect();
                    const tooltipWidth = 500;
                    const tooltipHeight = 600;
                    const spacing = 10;
                    
                    // Pozicija iznad ikonice
                    let top = rect.top - tooltipHeight - spacing;
                    let left = rect.left;
                    
                    // Ako nema mesta iznad, pozicioniraj ispod
                    if (top < 0) {
                        top = rect.bottom + spacing;
                    }
                    
                    // Ako nema mesta sa desne strane, pozicioniraj sa leve
                    if (left + tooltipWidth > window.innerWidth) {
                        left = window.innerWidth - tooltipWidth - 20;
                    }
                    
                    // Osiguraj da ne ide van ekrana sa leve strane
                    if (left < 20) {
                        left = 20;
                    }
                    
                    tooltip.style.top = top + 'px';
                    tooltip.style.left = left + 'px';
                }
                
                trigger.addEventListener('mouseenter', function() {
                    tooltip.style.display = 'block';
                    positionTooltip();
                    setTimeout(() => {
                        tooltip.classList.remove('invisible');
                        tooltip.classList.add('opacity-100');
                    }, 10);
                });
                
                trigger.addEventListener('mouseleave', function() {
                    tooltip.classList.add('invisible');
                    tooltip.classList.remove('opacity-100');
                    setTimeout(() => {
                        tooltip.style.display = 'none';
                    }, 200);
                });
                
                tooltip.addEventListener('mouseenter', function() {
                    tooltip.classList.remove('invisible');
                    tooltip.classList.add('opacity-100');
                });
                
                tooltip.addEventListener('mouseleave', function() {
                    tooltip.classList.add('invisible');
                    tooltip.classList.remove('opacity-100');
                    setTimeout(() => {
                        tooltip.style.display = 'none';
                    }, 200);
                });
                
                // Repozicioniraj na scroll
                window.addEventListener('scroll', positionTooltip);
                window.addEventListener('resize', positionTooltip);
            }
            @endif
        });

        function openDisableModal() {
            document.getElementById('disableModal').classList.remove('hidden');
        }

        function closeDisableModal() {
            document.getElementById('disableModal').classList.add('hidden');
        }

        function openDocumentsModal() {
            document.getElementById('documentsModal').classList.remove('hidden');
            loadDocuments();
        }

        function closeDocumentsModal() {
            document.getElementById('documentsModal').classList.add('hidden');
        }

        function loadDocuments() {
            const listDiv = document.getElementById('documentsList');
            const isLocked = {{ $mobilnost->is_locked ? 'true' : 'false' }};
            listDiv.innerHTML = '<p class="text-gray-500 text-sm">Loading...</p>';

            fetch('{{ route("admin.mobility.documents", $mobilnost->id) }}')
                .then(res => res.json())
                .then(docs => {
                    listDiv.innerHTML = '';
                    if (docs.length === 0) {
                        listDiv.innerHTML = '<p class="text-gray-500 text-sm">Nema dokumenata.</p>';
                        return;
                    }
                    
                    // Order so defaults are first usually, but array order from DB should handle timestamps or ID.
                    // Requirement: First two files cannot be deleted. 'type' !== 'other'
                    
                    docs.forEach(doc => {
                        const isDeletable = doc.type === 'other';
                        
                        const item = document.createElement('div');
                        item.className = 'flex justify-between items-center bg-gray-50 p-2 rounded border border-gray-200';
                        
                        const nameSpan = document.createElement('span');
                        nameSpan.className = 'text-sm font-medium text-gray-700';
                        nameSpan.textContent = doc.name;
                        
                        item.appendChild(nameSpan);
                        
                        if (isDeletable && !isLocked) {
                            const delBtn = document.createElement('button');
                            delBtn.className = 'text-red-500 hover:text-red-700 text-sm font-bold px-2';
                            delBtn.innerHTML = '&times;';
                            delBtn.title = 'Obriši';
                            delBtn.onclick = () => deleteDocument(doc.id);
                            item.appendChild(delBtn);
                        } else {
                             const lockIcon = document.createElement('span');
                             lockIcon.className = 'text-gray-400 text-xs px-2';
                             // If it is deletable but locked, maybe simply don't show X.
                             // The original logic showed (System) for system files.
                             // Now I might want to show nothing for custom files if locked? Or maybe a lock icon?
                             // User said "User can only export word documents".
                             // Let's stick effectively to: if(system) -> System label. If (custom & locked) -> nothing/read-only.
                             if (!isDeletable) {
                                 lockIcon.textContent = '(System)';
                                 item.appendChild(lockIcon);
                             }
                        }
                        
                        listDiv.appendChild(item);
                    });
                })
                .catch(err => {
                    console.error(err);
                    listDiv.innerHTML = '<p class="text-red-500 text-sm">Greška pri učitavanju dokumenata.</p>';
                });
        }

        function uploadDocument() {
            const input = document.getElementById('newDocInput');
            const file = input.files[0];
            if (!file) return;

            const status = document.getElementById('uploadStatus');
            status.textContent = 'Uploading...';
            status.className = 'text-xs mt-1 text-gray-500';

            const formData = new FormData();
            formData.append('file', file);
            
            fetch('{{ route("admin.mobility.documents.upload", $mobilnost->id) }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(res => {
                if (!res.ok) throw new Error('Upload failed');
                return res.json();
            })
            .then(data => {
                status.textContent = 'Upload successful!';
                status.className = 'text-xs mt-1 text-green-600';
                input.value = ''; // clear input
                loadDocuments(); // reload list
                setTimeout(() => { status.textContent = ''; }, 2000);
            })
            .catch(err => {
                console.error(err);
                status.textContent = 'Upload failed.';
                status.className = 'text-xs mt-1 text-red-600';
            });
        }

        function deleteDocument(docId) {
            if (!confirm('Are you sure you want to delete this file?')) return;

            fetch(`/admin/mobility/{{ $mobilnost->id }}/documents/${docId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => {
                if (!res.ok) throw new Error('Delete failed');
                return res.json();
            })
            .then(() => {
                loadDocuments();
            })
            .catch(err => {
                console.error(err);
                alert('Failed to delete document.');
            });
        }

        function saveAllGrades() {
            const form = document.getElementById('gradesForm');
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            const msgSpan = document.getElementById('saveMessage');
            
            // Convert formData to nested object structure for 'grades' array
            const grades = {};
            for (let [key, value] of formData.entries()) {
                const match = key.match(/grades\[(\d+)\]/);
                if (match) {
                    grades[match[1]] = value;
                }
            }

            msgSpan.textContent = 'Saving...';
            msgSpan.className = 'text-sm font-medium text-gray-500';

            fetch(`/admin/mobility/{{ $mobilnost->id }}/grades`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ grades: grades })
            })
            .then(res => res.json())
            .then(data => {
                msgSpan.textContent = 'Sve ocjene uspješno sačuvane!';
                msgSpan.className = 'text-sm font-medium text-green-600';
                setTimeout(() => {
                    msgSpan.textContent = '';
                }, 3000);
            })
            .catch(err => {
                msgSpan.textContent = 'Greška prilikom čuvanja ocjene.';
                msgSpan.className = 'text-sm font-medium text-red-600';
                console.error(err);
            });
        }
    </script>
</x-app-layout>



