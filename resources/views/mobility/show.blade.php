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
                <form action="{{ route('admin.mobility.export-word', $mobilnost->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-semibold px-4 py-2 rounded-lg shadow-lg transform transition hover:scale-105">
                        Izvezi u Word
                    </button>
                </form>
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
                                                <span id="pdfTrigger-{{ $mobilnost->fakultet->id }}" class="text-red-600 font-bold cursor-pointer hover:text-red-800 text-lg transition-colors" title="Pregledaj PDF uputstvo">!</span>
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
            <a href="{{ $mobilnost->fakultet->uputstvo_file }}" target="_blank" class="text-blue-600 hover:text-blue-800 text-xs underline whitespace-nowrap ml-2">Otvori u novom prozoru</a>
        </div>
        <div id="pdfPagesContainer-{{ $mobilnost->fakultet->id }}" class="w-full h-[calc(100%-45px)] overflow-y-auto bg-gray-50 p-4" style="display: none;">
            @if($mobilnost->fakultet->uputstvo_preview)
                {{-- Koristi postojeći preview URL --}}
                <img src="{{ $mobilnost->fakultet->uputstvo_preview }}" 
                     alt="PDF Preview" 
                     class="w-full h-auto shadow-md rounded border border-gray-200 mb-4"
                     onerror="this.parentElement.innerHTML='<div class=\'p-4 text-center text-gray-500\'><p>Preview nije dostupan.</p><a href=\'{{ $mobilnost->fakultet->uputstvo_file }}\' target=\'_blank\' class=\'text-blue-600 underline\'>Otvori PDF direktno</a></div>'">
            @else
                {{-- Fallback na direktan PDF URL --}}
                <iframe src="{{ $mobilnost->fakultet->uputstvo_file }}" 
                        class="w-full h-full border-0"
                        frameborder="0"
                        onerror="this.parentElement.innerHTML='<div class=\'p-4 text-center text-gray-500\'><p>PDF se ne može prikazati.</p><a href=\'{{ $mobilnost->fakultet->uputstvo_file }}\' target=\'_blank\' class=\'text-blue-600 underline\'>Otvori PDF direktno</a></div>'">
                </iframe>
            @endif
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>

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



