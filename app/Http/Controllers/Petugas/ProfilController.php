<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\Petugas\UpdatePasswordPetugasRequest;
use App\Http\Requests\Petugas\UpdateProfilPetugasRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('Petugas.Profil.index', compact('user'));
    }

    public function updateProfile(UpdateProfilPetugasRequest $request)
    {
        $user = auth()->user();
        $oldName = $user->name;

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        ActivityLog::record(
            'Ubah Profil',
            "Petugas {$oldName} memperbarui informasi profil akun (Nama: {$user->name}, Email: {$user->email}).",
            $user->id
        );

        return redirect()->route('petugas.profil.index')->with('success', 'Profil akun Anda berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordPetugasRequest $request)
    {
        $user = auth()->user();

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::record(
            'Ubah Password',
            "Petugas {$user->name} berhasil mengubah kata sandi akun.",
            $user->id
        );

        return redirect()->route('petugas.profil.index')->with('success', 'Kata sandi berhasil diubah.');
    }
}
