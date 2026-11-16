
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Pengguna - PingPong Mania</title>

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f6fa;
            margin: 0;
            padding: 30px;
        }

        .container {
            max-width: 1100px;
            margin: auto;
        }

        .panel {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-top: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        }

        h2 {
            margin-top: 0;
        }

        .search-form {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .search-form input {
            flex: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        button, .btn {
            border: none;
            padding: 9px 13px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-size: 13px;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
        }

        .btn-danger {
            background: #dc2626;
            color: white;
        }

        .btn-secondary {
            background: #64748b;
            color: white;
        }

        .btn-success {
            background: #16a34a;
            color: white;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: #f8fafc;
        }

        select {
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }

        .action-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }

        .alert {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 15px;
        }

        .alert-success {
            background: #dcfce7;
            color: #166534;
        }

        .alert-danger {
            background: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body>
<div class="container">

    <h2>Manajemen Pengguna</h2>
    <p>Kelola akun pengguna PingPong Mania.</p>

    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
        Kembali ke Dashboard
    </a>

    <div class="panel">

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

        <form action="{{ route('admin.users') }}" method="GET" class="search-form">
            <input
                type="text"
                name="search"
                value="{{ $search }}"
                placeholder="Cari nama atau email pengguna..."
            >

            <button type="submit" class="btn-primary">Cari</button>

            <a href="{{ route('admin.users') }}" class="btn btn-secondary">
                Reset
            </a>
        </form>

        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($users as $user)
                        <tr>
                            <td>
                                {{ $loop->iteration + ($users->currentPage() - 1) * $users->perPage() }}
                            </td>

                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>

                            <td>
                                @if($user->role === 'super_admin')
                                    Super Admin
                                @elseif($user->role === 'admin')
                                    Admin
                                @else
                                    User
                                @endif
                            </td>

                            <td>
                                <div class="action-group">

                                    {{-- Hanya Super Admin yang dapat mengubah role --}}
                                    @if(auth()->user()->role === 'super_admin' && $user->role !== 'super_admin')

                                        <form
                                            action="{{ route('admin.users.role', $user->id) }}"
                                            method="POST"
                                            style="display:flex; gap:8px; align-items:center;"
                                        >
                                            @csrf

                                            <select name="role" required>
                                                <option value="user" {{ $user->role === 'user' ? 'selected' : '' }}>
                                                    User
                                                </option>

                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>
                                                    Admin
                                                </option>
                                            </select>

                                            <button type="submit" class="btn-success">
                                                Simpan
                                            </button>
                                        </form>

                                    @endif

                                    {{-- Admin dan Super Admin dapat menghapus akun User --}}
                                    @if($user->role === 'user')
                                        <form
                                            action="{{ route('admin.users.destroy', $user->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin ingin menghapus akun pengguna ini? Data akun yang dihapus tidak dapat dikembalikan.');"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="5" style="text-align:center;">
                                Pengguna tidak ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px;">
            {{ $users->appends(['search' => $search])->links() }}
        </div>

    </div>
</div>
</body>
</html>
