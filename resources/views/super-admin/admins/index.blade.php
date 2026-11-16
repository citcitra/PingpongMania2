@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Kelola Admin</h2>
            <p>Halaman khusus Super Admin untuk mengelola akun Admin Biasa.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5>Total Akun Admin</h5>
            <h3>{{ $totalAdmins }} / 5</h3>
            <p>Jumlah tersebut sudah termasuk akun Super Admin.</p>

            @if($totalAdmins < 5)
                <a href="{{ route('super-admin.admins.create') }}"
                   class="btn btn-primary">
                    + Tambah Admin
                </a>
            @else
                <button class="btn btn-secondary" disabled>
                    Batas Admin Tercapai
                </button>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            Daftar Admin Biasa
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($admins as $admin)
                            <tr>
                                <td>{{ $loop->iteration + ($admins->currentPage() - 1) * $admins->perPage() }}</td>
                                <td>{{ $admin->name }}</td>
                                <td>{{ $admin->email }}</td>
                                
                            <td>
                                <span class="badge badge-primary">
                                    Admin
                                </span>
                            </td>

                            <td>
                                <a href="{{ route('super-admin.admins.edit', $admin->id) }}"
                                class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <form action="{{ route('super-admin.admins.destroy', $admin->id) }}"
                                    method="POST"
                                    style="display:inline-block;"
                                    onsubmit="return confirm('Yakin ingin menghapus akun Admin ini? Data akun tidak dapat dikembalikan.');">

                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn btn-danger btn-sm">
                                        Hapus
                                    </button>
                                </form>
                            </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    Belum ada Admin Biasa.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $admins->links() }}
        </div>
    </div>
</div>
@endsection