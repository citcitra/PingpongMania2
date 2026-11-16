<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    
public function dashboard()
{
    $adminId = auth()->id();

    $totalUsers = User::where('role', 'user')
        ->where('admin_id', $adminId)
        ->count();

    return view('admin.dashboard', [
        'totalUsers' => $totalUsers,
        'totalAdmins' => User::whereIn('role', ['admin', 'super_admin'])->count(),
        'totalMembers' => $totalUsers,
    ]);
}


    

public function users(Request $request)
{
    $search = $request->input('search');

    // Admin hanya melihat User yang menjadi tanggung jawabnya.
    $query = User::where('role', 'user')
        ->where('admin_id', auth()->id());

    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        });
    }

    $users = $query->orderBy('id', 'desc')
        ->paginate(10)
        ->appends(['search' => $search]);

    return view('admin.users', compact('users', 'search'));
}

    
public function updateRole(\Illuminate\Http\Request $request, $id)
{
    $request->validate([
        'role' => 'required|in:admin,user',
    ]);

    $user = \App\User::findOrFail($id);

    // Mencegah perubahan role akun sendiri
    if ($user->id == auth()->id()) {
        return redirect()
            ->route('admin.users')
            ->with('error', 'Role akun yang sedang digunakan tidak dapat diubah.');
    }

    // Melindungi akun Super Admin
    if ($user->role === 'super_admin') {
        return redirect()
            ->route('admin.users')
            ->with('error', 'Role Super Admin tidak dapat diubah.');
    }

    // Memeriksa batas maksimal 5 akun Admin
if ($request->role === 'admin' && $user->role !== 'admin') {

    $totalAdmins = User::whereIn('role', ['admin', 'super_admin'])->count();

    if ($totalAdmins >= 5) {
        return redirect()
            ->route('admin.users')
            ->with('error', 'Jumlah Admin sudah mencapai batas maksimal 5 akun.');
    }
}

    $user->role = $request->role;
    $user->save();

    return redirect()
        ->route('admin.users')
        ->with('success', 'Role pengguna berhasil diperbarui.');
}


    // Menghapus akun pengguna
   public function destroy($id)
{
    $user = User::where('id', $id)
        ->where('role', 'user')
        ->where('admin_id', auth()->id())
        ->firstOrFail();

    $user->delete();

    return redirect()->route('admin.users')
        ->with('success', 'Akun pengguna berhasil dihapus.');
}
}
