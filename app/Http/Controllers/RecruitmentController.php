<?php

namespace App\Http\Controllers;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentOpening;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class RecruitmentController extends Controller
{
    public function profile(): View
    {
        $openings = RecruitmentOpening::withCount('candidates')->latest()->get();
        $candidates = RecruitmentCandidate::latest()->get();

        return view('recrutments.Profil', compact('openings', 'candidates'));
    }

    public function index(): View
    {
        $openings = RecruitmentOpening::withCount('candidates')->latest()->get();
        $candidates = RecruitmentCandidate::latest()->get();

        return view('recrutments.recrutment', compact('openings', 'candidates'));
    }

    public function openings(): View
    {
        $openings = RecruitmentOpening::withCount('candidates')->latest()->get();

        return view('recrutments.lowongan', compact('openings'));
    }

    public function storeOpening(Request $request): RedirectResponse
    {
        RecruitmentOpening::create($request->validate([
            'title' => 'required|string|max:255',
            'division' => 'required|string|max:255',
            'employment_type' => 'required|in:Full-time,Kontrak,Magang',
            'quota' => 'required|integer|min:1|max:255',
            'status' => 'required|in:Aktif,Review,Draft,Tutup',
            'priority' => 'nullable|in:Rendah,Sedang,Tinggi',
            'sla' => 'nullable|string|max:50',
            'description' => 'nullable|string|max:2000',
            'criteria' => 'nullable|string|max:2000',
        ]));

        return redirect()->route('recruitment.lowongan')->with('success', 'Lowongan berhasil ditambahkan.');
    }

    public function candidates(): View
    {
        $candidates = RecruitmentCandidate::with('opening')->latest()->get();
        $openings = RecruitmentOpening::whereIn('status', ['Aktif', 'Review'])->latest()->get();

        return view('recrutments.kandidat', compact('candidates', 'openings'));
    }

    public function storeCandidate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'recruitment_opening_id' => 'nullable|exists:recruitment_openings,id',
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'position' => 'required|string|max:255',
            'source' => 'required|in:Job Portal,Referral,LinkedIn,Walk-in',
            'stage' => 'required|in:Applied,Screening,Interview,Offering,Hired,Rejected',
            'score' => 'nullable|integer|min:0|max:100',
            'screening_notes' => 'nullable|string|max:2000',
            'cv' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);
        unset($data['cv']);

        if ($request->hasFile('cv')) {
            $data['cv_path'] = $request->file('cv')->store('recruitment/cv', 'public');
        }

        $data['score'] ??= 0;

        RecruitmentCandidate::create($data);

        return redirect()->route('recruitment.kandidat')->with('success', 'Kandidat berhasil ditambahkan.');
    }

    public function destroyCandidate(RecruitmentCandidate $candidate): RedirectResponse
    {
        if ($candidate->cv_path) {
            Storage::disk('public')->delete($candidate->cv_path);
        }

        $candidate->delete();

        return redirect()->route('recruitment.kandidat')->with('success', 'Kandidat berhasil dihapus.');
    }
}
