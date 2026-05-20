<x-app-layout>
    <div class="py-12 bg-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                
                <div class="flex justify-between items-center mb-8">
                    <h2 class="text-2xl font-black text-indigo-900 uppercase tracking-tighter">
                        📅 Rekap Ceklis: {{ $startOfMonth->translatedFormat('F Y') }}
                    </h2>
                    <div class="flex gap-2 text-[10px] font-bold">
                        <span class="px-2 py-1 bg-red-500 text-white rounded">JUMAT: LIBUR</span>
                        <span class="px-2 py-1 bg-yellow-400 text-gray-800 rounded">KUNING: 1 CEKLIS</span>
                        <span class="px-2 py-1 bg-green-500 text-white rounded">HIJAU: LENGKAP</span>
                    </div>
                </div>

                <div class="grid grid-cols-7 gap-2">
                    @php
                        // Nama Hari
                        $days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
                    @endphp
                    
                    @foreach($days as $day)
                        <div class="text-center font-black text-gray-400 uppercase text-xs mb-2">{{ $day }}</div>
                    @endforeach

                    @for($i = 1; $i < $startOfMonth->dayOfWeekIso; $i++)
                        <div class="h-24 bg-gray-50 rounded-lg border border-dashed border-gray-200"></div>
                    @endfor

                    @for($date = $startOfMonth->copy(); $date->lte($endOfMonth); $date->addDay())
                        @php
                            $dateString = $date->toDateString();
                            $dataHariIni = $checklists->get($dateString);
                            $hasPagi = $dataHariIni ? $dataHariIni->where('tipe_form', 'Pagi')->first() : null;
                            $hasPulang = $dataHariIni ? $dataHariIni->where('tipe_form', 'Pulang')->first() : null;
                            $isFriday = $date->isFriday();

                            // Logika Warna Kotak
                            $boxClass = 'bg-white border-gray-200';
                            if ($isFriday) {
                                $boxClass = 'bg-red-500 border-red-600 text-white';
                            } elseif ($hasPagi && $hasPulang) {
                                $boxClass = 'bg-green-500 border-green-600 text-white';
                            } elseif ($hasPagi || $hasPulang) {
                                $boxClass = 'bg-yellow-400 border-yellow-500 text-gray-800';
                            }
                        @endphp

                        <div class="h-32 border-2 rounded-xl p-2 transition hover:scale-105 {{ $boxClass }} relative shadow-sm">
                            <span class="font-black text-lg">{{ $date->day }}</span>
                            
                            <div class="mt-2 flex flex-col gap-1">
                                @if(!$isFriday)
                                    <a href="{{ route('checklists.create', ['type' => 'Pagi', 'date' => $dateString]) }}" 
                                       class="text-[9px] font-bold p-1 rounded text-center uppercase {{ $hasPagi ? 'bg-white/30 cursor-not-allowed' : 'bg-indigo-600 text-white hover:bg-indigo-700' }}">
                                        {{ $hasPagi ? '☀️ Terisi' : '☀️ Ceklis Pagi' }}
                                    </a>
                                    
                                    <a href="{{ route('checklists.create', ['type' => 'Pulang', 'date' => $dateString]) }}" 
                                       class="text-[9px] font-bold p-1 rounded text-center uppercase {{ $hasPulang ? 'bg-white/30 cursor-not-allowed' : 'bg-gray-800 text-white hover:bg-gray-900' }}">
                                        {{ $hasPulang ? '🌙 Terisi' : '🌙 Ceklis Sore' }}
                                    </a>
                                @else
                                    <div class="absolute inset-0 flex items-center justify-center opacity-20">
                                        <span class="font-black text-2xl rotate-12">LIBUR</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</x-app-layout>