<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-8 rounded-lg shadow-lg border-t-8 border-indigo-600">
                <h2 class="text-2xl font-bold mb-6">Buat Template Ceklis (Google Form JONUSA)</h2>
                
                <form action="{{ route('admin.storeTemplate') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block font-bold">Pilih Divisi:</label>
                            <select name="division_id" class="w-full border-gray-300 rounded-md" required>
                                <option value="">-- Pilih Divisi --</option>
                                @foreach($divisions as $div)
                                    <option value="{{ $div->id }}">{{ $div->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block font-bold">Waktu Ceklis:</label>
                            <select name="tipe_form" class="w-full border-gray-300 rounded-md" required>
                                <option value="Pagi">Pagi (Mulai Kerja)</option>
                                <option value="Pulang">Pulang (Selesai Kerja)</option>
                            </select>
                        </div>
                    </div>

                    <div id="question-container" class="space-y-6">
                        </div>

                    <div class="mt-6">
                        <button type="button" onclick="addQuestion()" class="bg-green-500 text-white px-4 py-2 rounded-md font-bold hover:bg-green-600 transition">
                            + Tambah Pertanyaan
                        </button>
                    </div>

                    <div class="mt-10 pt-6 border-t">
                        <button type="submit" class="w-full bg-indigo-600 text-white py-3 rounded-md font-bold text-lg hover:bg-indigo-700 shadow-lg">
                            Simpan Template Form
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let qIndex = 0;
        function addQuestion() {
            const container = document.getElementById('question-container');
            const html = `
                <div class="p-5 border-2 border-gray-100 rounded-xl bg-gray-50 relative" id="q-row-${qIndex}">
                    <div class="flex justify-between items-center mb-4">
                        <span class="bg-indigo-100 text-indigo-700 px-3 py-1 rounded-full text-xs font-bold uppercase">Pertanyaan #${qIndex + 1}</span>
                        <button type="button" onclick="this.parentElement.parentElement.remove()" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                    </div>
                    
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-semibold">Teks Pertanyaan:</label>
                            <input type="text" name="questions[${qIndex}][text]" placeholder="Contoh: Bagaimana kondisi kesehatan Anda?" class="w-full border-gray-300 rounded-lg shadow-sm" required>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-semibold">Tipe Input:</label>
                                <select name="questions[${qIndex}][type]" onchange="toggleOptions(${qIndex}, this.value)" class="w-full border-gray-300 rounded-lg shadow-sm">
                                    <option value="text">Isian Teks (Deskripsi)</option>
                                    <option value="dropdown">Pilihan (Dropdown/Radio)</option>
                                </select>
                            </div>
                            <div id="options-container-${qIndex}" class="hidden">
                                <label class="text-sm font-semibold text-orange-600">Pilihan (Pisahkan dengan koma):</label>
                                <input type="text" name="questions[${qIndex}][options]" placeholder="Contoh: Sehat, Sakit, Izin" class="w-full border-gray-300 rounded-lg shadow-sm bg-orange-50">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
            qIndex++;
        }

        function toggleOptions(index, type) {
            const optContainer = document.getElementById(`options-container-${index}`);
            if (type === 'dropdown') {
                optContainer.classList.remove('hidden');
            } else {
                optContainer.classList.add('hidden');
            }
        }

        // Tambah satu pertanyaan otomatis saat halaman dibuka
        window.onload = addQuestion;
    </script>
</x-app-layout>