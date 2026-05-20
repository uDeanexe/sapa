<?php

namespace App\Http\Controllers;

use App\Mail\UserCreatedMail;
use App\Models\User;
use App\Models\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with('division')
            ->where('role', 'karyawan')
            ->orderBy('name', 'asc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json($users);
        }

        $divisions = Division::all();
        return view('kepala.user.index', compact('users', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|string|email|max:255|unique:users',
            'division_id' => 'required|exists:divisions,id',
        ]);

        $defaultPassword = 'jonusa123';

        $user = User::create([
            'name'                => $request->name,
            'email'               => $request->email,
            'password'            => $defaultPassword,
            'division_id'         => $request->division_id,
            'role'                => 'karyawan',
            'is_default_password' => true,
        ]);

        try {
            Mail::to($user->email)->send(new UserCreatedMail($user, $defaultPassword));
        } catch (Throwable $e) {
            Log::warning('Gagal mengirim email akun karyawan baru.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->back()
                ->with('success', "Karyawan berhasil didaftarkan! Password default: {$defaultPassword}")
                ->with('error', 'Akun berhasil dibuat, tetapi email pemberitahuan gagal dikirim. Periksa konfigurasi email server.');
        }

        return redirect()->back()->with('success', "Karyawan berhasil didaftarkan! Email pemberitahuan dikirim ke {$user->email}. Password default: {$defaultPassword}");
    }

    public function resetPassword($id)
    {
        $user = User::findOrFail($id);
        $user->password            = 'jonusa123';
        $user->is_default_password = true;
        $user->save();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Password {$user->name} berhasil direset ke jonusa123.",
            ]);
        }

        return redirect()->back()->with('success', "Password {$user->name} berhasil direset ke jonusa123.");
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);

            if ($user->id === auth()->id()) {
                return redirect()->back()->with('error', 'Anda tidak bisa menghapus akun sendiri!');
            }

            $user->delete();
            return redirect()->back()->with('success', 'Data karyawan berhasil dihapus dari sistem.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }
}
