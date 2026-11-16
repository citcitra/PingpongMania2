 @extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Tambah Admin Baru</h2>
            <p>Tambahkan akun Admin Biasa untuk mengelola sistem PingPong Mania.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Form Tambah Admin
        </div>

        <div class="card-body">
            <form action="{{ route('super-admin.admins.store') }}" method="POST">
                @csrf

                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name"
                        class="form-control"
                        value="{{ old('name') }}"
                        placeholder="Masukkan nama admin"
                        required>

                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email"
                        class="form-control"
                        value="{{ old('email') }}"
                        placeholder="Masukkan email admin"
                        required>

                    @error('email')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password"
                        class="form-control"
                        placeholder="Minimal 8 karakter"
                        required>

                    @error('password')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Konfirmasi Password</label>
                    <input type="password" name="password_confirmation"
                        class="form-control"
                        placeholder="Ulangi password"
                        required>
                </div>

                <div class="alert alert-info">
                    Role akun akan otomatis ditetapkan sebagai Admin.
                </div>

                <button type="submit" class="btn btn-primary">
                    Simpan Admin
                </button>

                <a href="{{ route('super-admin.admins.index') }}"
                    class="btn btn-secondary">
                    Kembali
                </a>
            </form>
        </div>
    </div>
</div>
@endsection