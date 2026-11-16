<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    use RegistersUsers;

    protected $redirectTo = '/user-dashboard';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
                'regex:/[^a-zA-Z0-9]/',
            ],
        ], [
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password.regex' => 'Password harus mengandung huruf, angka, dan karakter khusus.',
        ]);
    }

    protected function create(array $data)
    {
        return DB::transaction(function () use ($data) {

            // Mengunci data pengaturan agar dua registrasi
            // bersamaan tidak mendapatkan giliran yang sama.
            $settings = DB::table('user_assignment_settings')
                ->where('id', 1)
                ->lockForUpdate()
                ->first();

            if (!$settings) {
                throw new \RuntimeException(
                    'Pengaturan pembagian User belum tersedia.'
                );
            }

            // Mengambil Admin Biasa berdasarkan urutan ID.
            $admins = User::where('role', 'admin')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($admins->isEmpty()) {
                throw ValidationException::withMessages([
                    'email' => 'Pendaftaran belum dapat dilakukan karena belum tersedia Admin.',
                ]);
            }

            $lastAdminId = $settings->last_admin_id;

            // Memulai pencarian dari Admin setelah giliran terakhir.
            $startIndex = 0;

            if ($lastAdminId !== null) {
                foreach ($admins as $index => $admin) {
                    if ($admin->id == $lastAdminId) {
                        $startIndex = ($index + 1) % $admins->count();
                        break;
                    }

                    if ($admin->id > $lastAdminId) {
                        $startIndex = $index;
                        break;
                    }
                }
            }

            $selectedAdmin = null;

            // Memeriksa setiap Admin secara bergilir.
            for ($i = 0; $i < $admins->count(); $i++) {

                $index = ($startIndex + $i) % $admins->count();

                $admin = $admins[$index];

                $totalUsers = User::where('role', 'user')
                    ->where('admin_id', $admin->id)
                    ->count();

                if ($totalUsers < 25) {
                    $selectedAdmin = $admin;
                    break;
                }
            }

            // Semua Admin sudah mencapai batas.
            if (!$selectedAdmin) {
                throw ValidationException::withMessages([
                    'email' => 'Pendaftaran belum dapat dilakukan karena seluruh Admin sudah mencapai batas maksimal 25 pengguna.',
                ]);
            }

            // Membuat akun User dan menetapkan Admin penanggung jawab.
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'user',
                'admin_id' => $selectedAdmin->id,
            ]);

            // Menyimpan posisi giliran terakhir.
            DB::table('user_assignment_settings')
                ->where('id', 1)
                ->update([
                    'last_admin_id' => $selectedAdmin->id,
                    'updated_at' => now(),
                ]);

            return $user;
        });
    }
}
