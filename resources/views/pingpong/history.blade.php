@extends('layouts.app')

@section('title', 'Riwayat Latihan — PingPong Mania')

@section('content')

<div class="mb-4">
    <div class="eyebrow">TRAINING LOG</div>
    <h1 class="page-title">Riwayat Latihan</h1>
    <div class="muted">
        Riwayat latihan yang ditampilkan hanya milik akun kamu.
    </div>
</div>

<div class="panel">

    {{-- FILTER RIWAYAT --}}
    <form method="GET"
          action="{{ route('training.history') }}"
          class="form-row align-items-end">

        <div class="form-group col-md-4">
            <label for="mode">Mode Latihan</label>

            <select name="mode" id="mode" class="form-control">
                <option value="">Semua Mode</option>

                <option value="fixed"
                    {{ request('mode') === 'fixed' ? 'selected' : '' }}>
                    Fixed Mode
                </option>

                <option value="sequence"
                    {{ request('mode') === 'sequence' ? 'selected' : '' }}>
                    Sequence Mode
                </option>

                <option value="random"
                    {{ request('mode') === 'random' ? 'selected' : '' }}>
                    Random Mode
                </option>
            </select>
        </div>

        <div class="form-group col-md-4">
            <label for="date">Tanggal</label>

            <input
                type="date"
                id="date"
                name="date"
                value="{{ request('date') }}"
                class="form-control"
            >
        </div>

        <div class="form-group col-md-4">
            <button type="submit" class="btn btn-primary">
                Cari
            </button>

            <a href="{{ route('training.history') }}"
               class="btn btn-light">
                Reset
            </a>
        </div>

    </form>

    {{-- TABEL RIWAYAT --}}
    <div class="table-responsive">

        <table class="table">

            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Mode</th>
                    <th>Kecepatan</th>
                    <th>Interval</th>
                    <th>Arah / Urutan</th>
                    <th>Status</th>
                    <th>Durasi</th>
                    <th>Skor</th>
                </tr>
            </thead>

            <tbody>

                @forelse($sessions as $session)

                    <tr>

                        {{-- TANGGAL --}}
                        <td>
                            {{ $session->created_at->format('d M Y H:i') }}
                        </td>

                        {{-- MODE --}}
                        <td>
                            @if($session->mode === 'fixed')
                                <span class="badge badge-primary">
                                    Fixed Mode
                                </span>
                            @elseif($session->mode === 'sequence')
                                <span class="badge badge-info">
                                    Sequence Mode
                                </span>
                            @elseif($session->mode === 'random')
                                <span class="badge badge-success">
                                    Random Mode
                                </span>
                            @else
                                {{ ucfirst($session->mode) }}
                            @endif
                        </td>

                        {{-- KECEPATAN --}}
                        <td>
                            @if($session->speed === 'low')
                                Rendah
                            @elseif($session->speed === 'medium')
                                Sedang
                            @elseif($session->speed === 'high')
                                Tinggi
                            @else
                                {{ ucfirst($session->speed) }}
                            @endif
                        </td>

                        {{-- INTERVAL --}}
                        <td>
                            {{ $session->interval_seconds }} detik
                        </td>

                        {{-- ARAH / URUTAN --}}
                        <td>

                            @if($session->mode === 'sequence' && $session->sequence)

                                @php
                                    $sequenceLabels = [
                                        'left' => 'Kiri',
                                        'center' => 'Tengah',
                                        'right' => 'Kanan'
                                    ];

                                    $sequenceDirections = explode(',', $session->sequence);
                                @endphp

                                @foreach($sequenceDirections as $direction)
                                    {{ isset($sequenceLabels[trim($direction)])
                                        ? $sequenceLabels[trim($direction)]
                                        : ucfirst(trim($direction)) }}@if(!$loop->last) → @endif
                                @endforeach

                            @elseif($session->mode === 'random')
                                Acak

                            @elseif($session->direction === 'left')
                                Kiri

                            @elseif($session->direction === 'center')
                                Tengah

                            @elseif($session->direction === 'right')
                                Kanan

                            @else
                                -
                            @endif

                        </td>

                        {{-- STATUS --}}
                        <td>
                            {{ ucfirst($session->status) }}
                        </td>

                        {{-- DURASI --}}
                        <td>
                            {{ floor($session->duration_seconds / 60) }}m
                            {{ $session->duration_seconds % 60 }}s
                        </td>

                        {{-- SKOR --}}
                        <td>
                            {{ $session->score === null
                                ? '—'
                                : $session->score . '%' }}
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="8" class="text-center muted py-4">
                            Belum ada data untuk filter ini.
                        </td>
                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $sessions->links() }}
    </div>

</div>

@endsection
