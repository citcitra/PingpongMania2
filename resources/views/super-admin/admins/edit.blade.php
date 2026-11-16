
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">

            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Edit Admin</h4>
                </div>

                <div class="card-body">

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('super-admin.admins.update', $admin->id) }}"
                          method="POST">

                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label>Nama Admin</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $admin->name) }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Email Admin</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email', $admin->email) }}"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Simpan Perubahan
                        </button>

                        <a href="{{ route('super-admin.admins.index') }}"
                           class="btn btn-secondary">
                            Kembali
                        </a>

                    </form>

                </div>
            </div>

        </div>
    </div>
</div>
@endsection
