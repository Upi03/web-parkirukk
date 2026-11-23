<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $users = $query->paginate(10)->withQueryString();

        return view('Admin.User.index', compact('users'));
    }

    public function store(StoreUserRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ]);

        ActivityLog::record(
            'Tambah User',
            "Admin " . auth()->user()->name . " membuat akun user baru: {$user->name} ({$user->email}) dengan role {$user->role}."
        );

        return redirect()->route('admin.users.index')->with('success', "User {$user->name} berhasil ditambahkan.");
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->has('is_active') ? $request->boolean('is_active') : true,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        ActivityLog::record(
            'Edit User',
            "Admin " . auth()->user()->name . " memperbarui data user: {$user->name} ({$user->email})."
        );

        return redirect()->route('admin.users.index')->with('success', "Data user {$user->name} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $userName = $user->name;
        $userEmail = $user->email;
        $user->delete();

        ActivityLog::record(
            'Hapus User',
            "Admin " . auth()->user()->name . " menghapus akun user: {$userName} ({$userEmail})."
        );

        return redirect()->route('admin.users.index')->with('success', "User {$userName} berhasil dihapus.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')->with('error', 'Anda tidak dapat mengubah status akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $actionText = $user->is_active ? 'mengaktifkan' : 'menonaktifkan';

        ActivityLog::record(
            'Ubah Status User',
            "Admin " . auth()->user()->name . " {$actionText} akun: {$user->name}."
        );

        return redirect()->route('admin.users.index')->with('success', "Status user {$user->name} berhasil diubah.");
    }
}
