<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\KpiEvaluation;
use App\Models\KpiIndicator;
use App\Models\KpiSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KpiController extends Controller
{
    public function form(): View
    {
        $indicators = KpiIndicator::latest()->get();
        $measurementMethods = KpiIndicator::MEASUREMENT_METHODS;

        return view('KPI.Formulir', compact('indicators', 'measurementMethods'));
    }

    public function storeIndicator(Request $request): RedirectResponse
    {
        KpiIndicator::create($request->validate([
            'area' => 'required|string|max:255',
            'indicator' => 'required|string|max:255',
            'weight' => 'required|integer|min:1|max:100',
            'target' => 'required|string|max:255',
            'measurement_method' => ['required', Rule::in(KpiIndicator::MEASUREMENT_METHODS)],
        ]));

        return redirect()->route('kpi.formulir')->with('success', 'Indikator KPI berhasil ditambahkan.');
    }

    public function updateIndicator(Request $request, KpiIndicator $indicator): RedirectResponse
    {
        abort_if($indicator->is_locked, 403, 'Indikator yang sudah dikunci tidak dapat diubah.');

        $indicator->update($request->validate([
            'area' => 'required|string|max:255',
            'indicator' => 'required|string|max:255',
            'weight' => 'required|integer|min:1|max:100',
            'target' => 'required|string|max:255',
            'measurement_method' => ['required', Rule::in(KpiIndicator::MEASUREMENT_METHODS)],
        ]));

        return redirect()->route('kpi.formulir')->with('success', 'Indikator KPI berhasil diperbarui.');
    }

    public function destroyIndicator(KpiIndicator $indicator): RedirectResponse
    {
        abort_if($indicator->is_locked, 403, 'Indikator yang sudah dikunci tidak dapat dihapus.');

        $indicator->delete();

        return redirect()->route('kpi.formulir')->with('success', 'Indikator KPI berhasil dihapus.');
    }

    public function lockIndicators(): RedirectResponse
    {
        $indicatorCount = KpiIndicator::count();
        $totalWeight = KpiIndicator::sum('weight');

        if ($indicatorCount === 0 || $totalWeight !== 100) {
            return redirect()
                ->route('kpi.formulir')
                ->with('error', 'Formulir hanya bisa dikunci setelah indikator tersedia dan total bobot tepat 100%.');
        }

        KpiIndicator::query()->where('is_locked', false)->update(['is_locked' => true]);

        return redirect()->route('kpi.formulir')->with('success', 'Formulir KPI berhasil dikunci.');
    }

    public function schedules(): View
    {
        $schedules = KpiSchedule::latest('start_date')->get();
        $divisionOptions = $this->divisionOptions();

        return view('KPI.Jadwal', compact('schedules', 'divisionOptions'));
    }

    public function storeSchedule(Request $request): RedirectResponse
    {
        $indicatorCount = KpiIndicator::count();
        $totalWeight = KpiIndicator::sum('weight');

        if ($indicatorCount === 0 || $totalWeight !== 100) {
            return redirect()
                ->route('kpi.jadwal')
                ->with('error', 'Lengkapi formulir KPI dan pastikan total bobot 100% sebelum membuat jadwal.');
        }

        $data = $request->validate([
            'period' => 'required|string|max:255',
            'division' => ['required', 'string', 'max:255', Rule::in($this->divisionOptions())],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:Berjalan,Terjadwal,Draft,Selesai',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data['progress'] ??= 0;
        KpiSchedule::create($data);

        return redirect()->route('kpi.jadwal')->with('success', 'Jadwal KPI berhasil ditambahkan.');
    }

    public function updateSchedule(Request $request, KpiSchedule $schedule): RedirectResponse
    {
        $data = $request->validate([
            'period' => 'required|string|max:255',
            'division' => ['required', 'string', 'max:255', Rule::in($this->divisionOptions())],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:Berjalan,Terjadwal,Draft,Selesai',
            'progress' => 'nullable|integer|min:0|max:100',
            'notes' => 'nullable|string|max:2000',
        ]);

        $data['progress'] ??= 0;
        $schedule->update($data);

        return redirect()->route('kpi.jadwal')->with('success', 'Jadwal KPI berhasil diperbarui.');
    }

    public function destroySchedule(KpiSchedule $schedule): RedirectResponse
    {
        $schedule->delete();

        return redirect()->route('kpi.jadwal')->with('success', 'Jadwal KPI berhasil dihapus.');
    }

    public function evaluations(): View
    {
        $evaluations = KpiEvaluation::latest()->get();

        return view('KPI.Evaluasi', compact('evaluations'));
    }

    public function storeEvaluation(Request $request): RedirectResponse
    {
        if (KpiSchedule::count() === 0) {
            return redirect()
                ->route('kpi.evaluasi')
                ->with('error', 'Buat jadwal KPI terlebih dahulu sebelum menambahkan evaluasi.');
        }

        $data = $request->validate([
            'employee_name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'period' => 'required|string|max:255',
            'score' => 'required|integer|min:0|max:100',
            'status' => 'required|in:Draft,Review,Final',
            'note' => 'nullable|string|max:2000',
        ]);

        KpiEvaluation::create($data);

        return redirect()->route('kpi.evaluasi')->with('success', 'Evaluasi KPI berhasil ditambahkan.');
    }

    public function updateEvaluation(Request $request, KpiEvaluation $evaluation): RedirectResponse
    {
        $evaluation->update($request->validate([
            'employee_name' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'period' => 'required|string|max:255',
            'score' => 'required|integer|min:0|max:100',
            'status' => 'required|in:Draft,Review,Final',
            'note' => 'nullable|string|max:2000',
        ]));

        return redirect()->route('kpi.evaluasi')->with('success', 'Evaluasi KPI berhasil diperbarui.');
    }

    public function finalizeEvaluation(KpiEvaluation $evaluation): RedirectResponse
    {
        if ($evaluation->status === 'Final') {
            return redirect()->route('kpi.evaluasi')->with('success', 'Evaluasi KPI sudah final.');
        }

        $evaluation->update(['status' => 'Final']);

        return redirect()->route('kpi.evaluasi')->with('success', 'Evaluasi KPI berhasil difinalkan.');
    }

    public function destroyEvaluation(KpiEvaluation $evaluation): RedirectResponse
    {
        $evaluation->delete();

        return redirect()->route('kpi.evaluasi')->with('success', 'Evaluasi KPI berhasil dihapus.');
    }

    private function divisionOptions(): array
    {
        return array_merge(
            ['Semua Divisi'],
            Division::query()->orderBy('name')->pluck('name')->all()
        );
    }
}
