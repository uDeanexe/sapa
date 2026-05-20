<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\FormTemplate;
use App\Models\DailyChecklist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ChecklistController extends Controller
{
    /**
     * SISI ADMIN: Daftar Template yang sudah dibuat (Riwayat)
     */
    public function indexTemplate()
    {
        $templates = FormTemplate::with('division')->latest()->get();
        return view('admin.checklists.index', compact('templates'));
    }

    /**
     * SISI ADMIN: Halaman buat pertanyaan baru
     */
    public function createTemplate()
    {
        $divisions = Division::all();
        return view('admin.checklists.create_template', compact('divisions'));
    }

    /**
     * SISI ADMIN: Simpan template ke JSON
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'division_id' => 'required',
            'tipe_form' => 'required',
            'questions' => 'required|array',
        ]);

        FormTemplate::create([
            'division_id' => $request->division_id,
            'tipe_form' => $request->tipe_form,
            'questions' => $request->questions,
        ]);

        return redirect()->route('admin.indexTemplate')->with('success', 'Template Form Berhasil Dibuat!');
    }

    /**
     * SISI KARYAWAN: Tampilan Kalender Utama
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);
        
        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth();
        $endOfMonth = $startOfMonth->copy()->endOfMonth();

        // GroupBy date agar mudah dicek di Blade
        $checklists = DailyChecklist::where('user_id', $user->id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get()
            ->groupBy(function($data) {
                return Carbon::parse($data->date)->format('Y-m-d');
            });

        return view('checklists.index', compact('startOfMonth', 'endOfMonth', 'checklists'));
    }

    /**
     * SISI KARYAWAN: Buka Form berdasarkan klik di Kalender
     */
    public function create($type, $date)
    {
        $user = Auth::user();
        $template = FormTemplate::where('division_id', $user->division_id)
                    ->where('tipe_form', $type)
                    ->first();

        if (!$template) {
            return redirect()->back()->with('error', 'Template untuk divisi Anda belum dibuat.');
        }

        return view('checklists.fill', compact('template', 'type', 'date'));
    }

    /**
     * SISI KARYAWAN: Simpan Jawaban
     */
    public function storeAnswer(Request $request)
    {
        // Pastikan input 'date' ada di form fill.blade.php kamu
        DailyChecklist::create([
            'user_id' => Auth::id(),
            'answers' => $request->answers,
            'date' => $request->date, // Mengambil tanggal dari tombol yang diklik, bukan hari ini saja
            'tipe_form' => $request->tipe_form,
        ]);

        return redirect()->route('checklists.index')->with('success', 'Ceklis berhasil disimpan!');
    }
}