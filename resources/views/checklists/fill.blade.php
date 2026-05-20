<x-app-layout>
    <div class="py-12 bg-gray-50">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-indigo-600 h-3 rounded-t-xl shadow-lg"></div>
            <div class="bg-white p-8 rounded-b-xl shadow-md mb-6 border border-gray-100">
                <h1 class="text-3xl font-black text-gray-800 tracking-tight">Ceklis {{ $template->tipe_form }} - JONUSA</h1>
                <p class="text-gray-500 mt-2 font-medium">Divisi: <span class="text-indigo-600 font-bold uppercase">{{ $template->division->name }}</span></p>
                <hr class="my-6 border-gray-100">
                <p class="text-xs font-bold text-red-500 uppercase tracking-widest">* Wajib diisi untuk kepatuhan kerja</p>
            </div>

            <form action="{{ route('checklists.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="tipe_form" value="{{ $template->tipe_form }}">

                @foreach($template->questions as $index => $q)
                <div class="bg-white p-6 rounded-xl shadow-sm mb-4 border border-gray-200 transition hover:border-indigo-300">
                    <label class="block mb-4 font-bold text-gray-700 text-lg">
                        {{ $q['text'] }} <span class="text-red-500">*</span>
                    </label>

                    @if($q['type'] == 'dropdown')
                        <div class="space-y-3">
                            @foreach($q['options'] as $option)
                            <label class="flex items-center p-3 border rounded-lg hover:bg-indigo-50 cursor-pointer transition">
                                <input type="radio" name="answers[{{ $index }}]" value="{{ $option }}" class="text-indigo-600 focus:ring-indigo-500 mr-3" required>
                                <span class="text-gray-700 font-medium">{{ $option }}</span>
                            </label>
                            @endforeach
                        </div>
                    @else
                        <textarea name="answers[{{ $index }}]" class="w-full border-gray-200 rounded-xl shadow-inner focus:ring-indigo-500 focus:border-indigo-500 text-sm" rows="3" placeholder="Jawaban Anda..." required></textarea>
                    @endif
                </div>
                @endforeach

                <div class="bg-red-50 p-6 rounded-xl shadow-sm mb-8 border-l-8 border-red-500">
                    <h3 class="font-black text-red-700 mb-2 uppercase text-xs tracking-widest">Pernyataan Kepatuhan:</h3>
                    <p class="text-[11px] text-gray-600 leading-relaxed font-semibold uppercase">
                        SAYA MENGERTI & SIAP MEMATUHI. (DILARANG GAME/SOSMED, JAGA RAHASIA DATA, ETIKA SOPAN, KEJUJURAN LAPORAN, DAN PRODUKTIVITAS ASET).
                    </p>
                    <label class="flex items-center mt-6 p-3 bg-white rounded-lg border border-red-200 cursor-pointer">
                        <input type="checkbox" required class="rounded text-red-600 mr-3 w-5 h-5">
                        <span class="text-sm font-black text-red-700 uppercase tracking-tighter">SAYA MENGERTI & SIAP MEMATUHI</span>
                    </label>
                </div>

                <div class="flex justify-between items-center pb-12">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-12 py-3 rounded-xl font-black uppercase tracking-widest transition shadow-xl active:scale-95">
                        Kirim Laporan
                    </button>
                    <span class="text-gray-400 text-[10px] font-bold uppercase italic">Notify-JONUSA Secure Form</span>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>