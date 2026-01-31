<x-app-layout>
    {{-- <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot> --}}

    <div class="py-10 max-w-7xl mx-auto px-6">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Kontrolna tabla</h1>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-8" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-xl overflow-hidden border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-semibold text-gray-800">Zahtjevi za prepis</h2>
                <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded-full">{{ $mappingRequests->count() }} Ukupno</span>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fakultet</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Datum slanja</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Broj predmeta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Radnje</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($mappingRequests as $request)
                            <tr class="hover:bg-gray-50 transition-colors duration-150 ease-in-out">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">{{ $request->fakultet?->naziv ?? 'Nepoznat fakultet' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->created_at->format('d.m.Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                    {{ $request->subjects->where('professor_id', auth()->id())->count() }} / {{ $request->subjects->count() }} predmeta
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        // Global status
                                        $status = $request->status;
                                        $color = '';
                                        $text = ucfirst($status);

                                        if ($status == 'accepted') {
                                            // $text = 'Prihvaćen'; // Already translated in code logic above? No, $status is 'accepted' string.
                                            // The code block uses `ucfirst($status)` initially.
                                            // Let's override.
                                            $text = 'Prihvaćen';
                                            $color = 'bg-green-100 text-green-800';
                                        } elseif ($status == 'rejected') {
                                            $text = 'Odbijen';
                                            $color = 'bg-red-100 text-red-800';
                                        } else {
                                            // Request is pending globally. Check MY status.
                                            $mySubjects = $request->subjects->where('professor_id', auth()->id());
                                            $total = $mySubjects->count();
                                            $matched = $mySubjects->whereNotNull('fit_predmet_id')->count();
                                            $rejected = $mySubjects->where('is_rejected', true)->count();
                                            $processed = $matched + $rejected;

                                            if ($total > 0 && $processed == $total) {
                                                // I have finished my part
                                                if ($matched == 0 && $rejected > 0) {
                                                    // I rejected everything
                                                    $text = 'Odbijeno';
                                                    $color = 'bg-red-100 text-red-800';
                                                } else {
                                                    // I matched some (or all)
                                                    $text = 'Poslato';
                                                    $color = 'bg-blue-100 text-blue-800';
                                                }
                                            } else {
                                                // Still have work to do
                                                $text = 'Potrebna akcija';
                                                $color = 'bg-yellow-100 text-yellow-800';
                                            }
                                        }
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                                        {{ $text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('mapping-request.show', $request->id) }}" class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 px-3 py-1 rounded-md transition-colors">
                                        Pregledaj i poveži
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-10 text-center text-gray-500">
                                    Nema pronađenih zahtjeva za prepis.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
