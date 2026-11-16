<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\User;

class SuperAdminController extends Controller
{
    // Menampilkan daftar Admin Biasa
    public function index()
    {
        $admins = User::where('role', 'admin')
                      ->orderBy('id', 'desc')
                      ->paginate(10);

        $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();

        return view('super-admin.admins.index', compact('admins', 'totalAdmins'));
    }

    // Menampilkan halaman tambah Admin
    public function create()
    {
        $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();

        if ($totalAdmins >= 5) {
            return redirect()->route('super-admin.admins.index')
                ->with('error', 'Jumlah admin sudah mencapai batas maksimal 5 akun.');
        }

        return view('super-admin.admins.create');
    }

    // Menyimpan akun Admin baru
    public function store(Request $request)
    {
        $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();

        if ($totalAdmins >= 5) {
            return redirect()->route('super-admin.admins.index')
                ->with('error', 'Jumlah admin sudah mencapai batas maksimal 5 akun.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $admin = new User;
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->password = Hash::make($request->password);
        $admin->role = 'admin';
        $admin->save();

        return redirect()->route('super-admin.admins.index')
            ->with('success', 'Akun Admin berhasil ditambahkan.');
    }

    
    // Menampilkan halaman edit Admin
    public function edit($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        return view('super-admin.admins.edit', compact('admin'));
    }

    // Memperbarui data Admin
    public function update(Request $request, $id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $admin->id,
        ]);

        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->save();

        return redirect()->route('super-admin.admins.index')
            ->with('success', 'Data Admin berhasil diperbarui.');
    }

    // Menghapus akun Admin
    public function destroy($id)
    {
        $admin = User::where('role', 'admin')->findOrFail($id);

        if ($admin->id == auth()->id()) {
            return redirect()->route('super-admin.admins.index')
                ->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        $admin->delete();

        return redirect()->route('super-admin.admins.index')
            ->with('success', 'Akun Admin berhasil dihapus.');
    }

}