<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
   public function store(Request $request)
{
    $request->validate([
        'type' => 'required|in:cuti,izin,sakit',
        'category' => 'required',
        'start_date' => 'required|date',
        'end_date' => 'required|date',
        'reason' => 'required',
        'attachment' => 'nullable|image|max:2048', // Mendukung upload foto surat dokter
    ]);

    $path = null;
    if ($request->hasFile('attachment')) {
        // Simpan ke folder public/attachments
        $path = $request->file('attachment')->store('attachments', 'public');
    }

    $permission = Permission::create([
        'user_id' => $request->user()->id,
        'type' => $request->type,
        'category' => $request->category,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'reason' => $request->reason,
        'attachment' => $path,
        'status' => 'pending',
    ]);

    return response()->json(['success' => true, 'message' => 'Berhasil'], 201);
}
}