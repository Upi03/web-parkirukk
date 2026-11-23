<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdatePasswordRequest;
use App\Http\Requests\Admin\UpdateProfileRequest;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('Admin.Profil.index', compact('user'));
    }

    public function updateProfile(UpdateProfileRequest $request)
    {
        $user = auth()->user();
        $oldName = $user->name;
        
        $user->update([
            'name' => $request->name,
            'email' => $request->email,
        ]);

        ActivityLog::record(
            'Ubah Profil',
            "User {$oldName} memperbarui informasi profil akun (Nama: {$user->name}, Email: {$user->email})."
        );

        return redirect()->route('admin.profil.index')->with('success', 'Profil Anda berhasil diperbarui.');
    }

    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = auth()->user();
        
        $user->update([
            'password' => Hash::make($request->password),
        ]);

        ActivityLog::record(
            'Ubah Password',
            "User {$user->name} berhasil mengubah password akun."
        );

        return redirect()->route('admin.profil.index')->with('success', 'Password akun berhasil diubah.');
    }
}
