@extends('layouts.app')

@section('title', 'Dashboard — PingPong Mania')

@section('content')

<div class="d-flex justify-content-between align-items-start flex-wrap mb-4">
    <div>
        <div class="eyebrow">DASHBOARD PENGGUNA</div>
        <h1 class="page-title">Halo, {{ Auth::user()->name }} 👋</h1>
        <div class="muted">Siap meningkatkan skill pingpong hari ini?</div>
    </div>

    <span class="control-status mt-2">
        <span id="status-dot" class="status-dot {{ $activeSession ? 'running' : '' }}"></span>
        <span id="status-text">
            {{ $activeSession ? 'Latihan berjalan' : 'Mesin tidak aktif' }}
        </span>
    </span>
</div>

{{-- STATISTIK --}}
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon">▣</div>
            <div class="stat-number">{{ $totalSessions }}</div>
            <div class="stat-label">Sesi latihan selesai</div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon">◉</div>
            <div class="stat-number">
                {{ $averageScore === null ? '—' : number_format($averageScore, 0) . '%' }}
            </div>
            <div class="stat-label">Skor rata-rata</div>
        </div>
    </div>

    <div class="col-md-4 mb-3">
        <div class="stat-card">
            <div class="stat-icon">◷</div>
            <div class="stat-number">
                {{ floor($totalSeconds / 3600) }}j
                {{ floor(($totalSeconds % 3600) / 60) }}m
            </div>
            <div class="stat-label">Total durasi latihan selesai</div>
        </div>
    </div>
</div>

{{-- HERO --}}
<div class="hero-banner mb-4">
    <div class="eyebrow" style="color:#91c7ff">YOUR NEXT SESSION</div>
    <h3 class="mt-2">Latihan yang konsisten, progres yang nyata.</h3>
    <p class="mb-3">
        Pilih mode latihan dan sesuaikan pengaturan mesin sebelum memulai.
        Kamu juga bisa mengubah pengaturan saat latihan berlangsung.
    </p>
    <a href="#mode-latihan" class="btn btn-light">Atur latihan &nbsp; ↓</a>
</div>

<div class="row" id="mode-latihan">

    {{-- PENGATURAN MESIN --}}
    <div class="col-lg-7 mb-4">
        <div class="panel h-100">
            <div class="panel-title">Pengaturan Mesin</div>

            <div id="control-alert" class="alert d-none" role="alert"></div>

            <form id="machine-form">

                {{-- MODE --}}
                <div class="form-group">
                    <label for="mode">Mode Latihan</label>

                    <select class="form-control" id="mode" name="mode">
                        <option value="fixed">Fixed Mode</option>
                        <option value="sequence">Sequence Mode</option>
                        <option value="random">Random Mode</option>
                    </select>

                    <small class="muted">
                        Pilih pola latihan yang ingin digunakan.
                    </small>
                </div>

                {{-- KETERANGAN MODE --}}
                <div class="alert alert-info" id="mode-description">
                    Fixed Mode: bola diarahkan terus-menerus ke satu arah pilihan.
                </div>

                {{-- ARAH FIXED --}}
                <div class="form-group" id="fixed-direction-group">
                    <label>Arah Bola</label>

                    <div class="d-flex flex-wrap" role="group">
                        <label class="btn btn-outline-primary mr-2 mb-2">
                            <input type="radio" name="direction" value="left">
                            Kiri
                        </label>

                        <label class="btn btn-outline-primary mr-2 mb-2">
                            <input type="radio" name="direction" value="center" checked>
                            Tengah
                        </label>

                        <label class="btn btn-outline-primary mb-2">
                            <input type="radio" name="direction" value="right">
                            Kanan
                        </label>
                    </div>
                </div>

                {{-- URUTAN SEQUENCE --}}
                <div class="form-group d-none" id="sequence-group">
                    <label for="sequence">Urutan Arah Bola</label>

                    <input
                        type="text"
                        class="form-control"
                        id="sequence"
                        name="sequence"
                        value="right,center,left"
                        placeholder="Contoh: right,center,left"
                    >

                    <small class="muted">
                        Gunakan pemisah koma. Pilihan arah:
                        right, center, left.
                    </small>

                    <div class="mt-2">
                        <strong>Contoh urutan:</strong>
                        <div class="d-flex flex-wrap mt-2">
                            <span class="badge badge-primary mr-2">Kanan</span>
                            <span class="badge badge-primary mr-2">Tengah</span>
                            <span class="badge badge-primary">Kiri</span>
                        </div>
                    </div>
                </div>

                {{-- RANDOM --}}
                <div class="alert alert-secondary d-none" id="random-description">
                    Random Mode: sistem menentukan arah bola secara acak
                    antara kiri, tengah, dan kanan.
                </div>

                {{-- KECEPATAN --}}
                <div class="form-group">
                    <label for="speed">Tingkat Kecepatan</label>

                    <select class="form-control" id="speed" name="speed">
                        <option value="low">Rendah</option>
                        <option value="medium" selected>Sedang</option>
                        <option value="high">Tinggi</option>
                    </select>
                </div>

                {{-- INTERVAL --}}
                <div class="form-group">
                    <label for="interval_seconds">Interval Pelontaran</label>

                    <select class="form-control" id="interval_seconds" name="interval_seconds">
                        <option value="1">1 detik</option>
                        <option value="2" selected>2 detik</option>
                        <option value="3">3 detik</option>
                        <option value="4">4 detik</option>
                        <option value="5">5 detik</option>
                    </select>
                </div>

                {{-- TOMBOL START DAN STOP --}}
                <div class="d-flex flex-wrap align-items-center mt-4">

                    <button
                        type="button"
                        id="start-btn"
                        class="btn btn-primary mr-2 mb-2">
                        ▶ START
                    </button>

                    <button
                        type="button"
                        id="stop-btn"
                        class="btn btn-danger mb-2">
                        ■ STOP
                    </button>

                </div>

                <small class="muted d-block mt-2">
                    START akan memulai latihan atau memperbarui pengaturan
                    jika mesin sedang berjalan. STOP akan menghentikan latihan.
                </small>

                <small class="muted d-block mt-1">
                    Perintah dikirim ke Raspberry Pi Pico WH melalui jaringan lokal.
                    Pastikan perangkat terhubung sebelum memulai latihan.
                </small>

            </form>
        </div>
    </div>

    {{-- PANDUAN KONEKSI --}}
    <div class="col-lg-5 mb-4">
        <div class="panel h-100">

            <div class="panel-title">Panduan Koneksi</div>

            <div class="d-flex mb-3">
                <div class="stat-icon mr-3">①</div>
                <div>
                    <strong>Pastikan Raspberry Pi aktif</strong>
                    <div class="muted">
                        Website dan Pico WH harus berada pada jaringan lokal
                        yang dapat saling menjangkau.
                    </div>
                </div>
            </div>

            <div class="d-flex mb-3">
                <div class="stat-icon mr-3">②</div>
                <div>
                    <strong>Periksa konfigurasi server</strong>
                    <div class="muted">
                        Pastikan RASPBERRY_PI_URL sudah diatur pada file .env.
                    </div>
                </div>
            </div>

            <div class="d-flex">
                <div class="stat-icon mr-3">③</div>
                <div>
                    <strong>Gunakan emergency stop fisik</strong>
                    <div class="muted">
                        Jangan jadikan tombol web sebagai satu-satunya
                        cara menghentikan mesin.
                    </div>
                </div>
            </div>

            <hr>

            <div class="panel-title">Sesi Terakhir</div>

            @if($activeSession)

                <span class="badge-soft">Sedang berjalan</span>

                <div class="muted mt-2">
                    {{ ucfirst($activeSession->mode) }}
                    · {{ ucfirst($activeSession->speed) }}
                    · {{ $activeSession->interval_seconds }} detik
                </div>

            @else

                <div class="muted">
                    Belum ada sesi aktif. Tekan START untuk memulai latihan.
                </div>

            @endif

        </div>
    </div>

</div>

{{-- RIWAYAT --}}
<div class="panel">

    <div class="d-flex justify-content-between align-items-center flex-wrap">
        <div class="panel-title mb-0">Riwayat Latihan Terbaru</div>

        <a href="{{ route('training.history') }}"
            class="btn btn-outline-primary btn-sm">
            Lihat semua
        </a>
    </div>

    <div class="table-responsive mt-3">
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
                </tr>
            </thead>

            <tbody>

                @forelse($sessions as $session)

                    <tr>
                        <td>{{ $session->created_at->format('d M Y H:i') }}</td>

                        <td>
                            @if($session->mode === 'fixed')
                                Fixed Mode
                            @elseif($session->mode === 'sequence')
                                Sequence Mode
                            @elseif($session->mode === 'random')
                                Random Mode
                            @else
                                {{ ucfirst($session->mode) }}
                            @endif
                        </td>

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

                        <td>{{ $session->interval_seconds }} detik</td>

                        <td>
                            @if($session->mode === 'sequence' && $session->sequence)
                                {{ str_replace(
                                    ['right', 'center', 'left'],
                                    ['Kanan', 'Tengah', 'Kiri'],
                                    $session->sequence
                                ) }}
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

                        <td>{{ ucfirst($session->status) }}</td>

                        <td>
                            {{ floor($session->duration_seconds / 60) }}m
                            {{ $session->duration_seconds % 60 }}s
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="text-center muted py-4">
                            Belum ada riwayat latihan.
                        </td>
                    </tr>

                @endforelse

            </tbody>
        </table>
    </div>

</div>

@endsection

@push('scripts')

<script>
(function () {

    var form = document.getElementById('machine-form');
    var alertBox = document.getElementById('control-alert');

    var mode = document.getElementById('mode');
    var fixedGroup = document.getElementById('fixed-direction-group');
    var sequenceGroup = document.getElementById('sequence-group');
    var randomDescription = document.getElementById('random-description');
    var modeDescription = document.getElementById('mode-description');

    // STATUS AWAL SESI
    var sessionIsActive = @json((bool) $activeSession);

    // MENGATUR TAMPILAN BERDASARKAN MODE
    function updateModeDisplay() {

        var selectedMode = mode.value;

        fixedGroup.classList.add('d-none');
        sequenceGroup.classList.add('d-none');
        randomDescription.classList.add('d-none');

        if (selectedMode === 'fixed') {

            fixedGroup.classList.remove('d-none');

            modeDescription.textContent =
                'Fixed Mode: bola diarahkan terus-menerus ke satu arah pilihan.';

        } else if (selectedMode === 'sequence') {

            sequenceGroup.classList.remove('d-none');

            modeDescription.textContent =
                'Sequence Mode: bola mengikuti urutan arah yang sudah ditentukan.';

        } else if (selectedMode === 'random') {

            randomDescription.classList.remove('d-none');

            modeDescription.textContent =
                'Random Mode: arah bola ditentukan secara acak oleh sistem.';
        }
    }

    mode.addEventListener('change', updateModeDisplay);

    updateModeDisplay();

    // PESAN NOTIFIKASI
    function showMessage(message, success) {

        alertBox.className = 'alert ' +
            (success ? 'alert-success' : 'alert-danger');

        alertBox.textContent = message;
    }

    // MENGAMBIL PENGATURAN FORM
    function settings() {

        var data = {};

        new FormData(form).forEach(function (value, key) {
            data[key] = value;
        });

        if (mode.value === 'random') {
            data.direction = 'random';
        }

        if (mode.value === 'sequence') {
            data.direction = 'sequence';
        }

        return data;
    }

    // MENGIRIM PERINTAH KE LARAVEL
    function send(action) {

        var payload = settings();
        payload.action = action;
        
        console.log('DATA DIKIRIM KE LARAVEL:', payload);

        var startButton = document.getElementById('start-btn');
        var stopButton = document.getElementById('stop-btn');

        startButton.disabled = true;
        stopButton.disabled = true;

        var endpoint = action === 'stop'
            ? '{{ route('machine.stop') }}'
            : '{{ route('machine.control') }}';

        fetch(endpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute('content')
            },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        })

        .then(function (response) {

            return response.json().then(function (body) {

                if (!response.ok || body.success === false) {
                    throw new Error(
                        body.message || 'Perintah gagal dikirim.'
                    );
                }

                return body;
            });

        })

        .then(function (body) {

            showMessage(
                body.message || 'Perintah berhasil diterima.',
                true
            );

            if (action === 'start') {

                sessionIsActive = true;

                document.getElementById('status-text').textContent =
                    'Latihan berjalan';

                document.getElementById('status-dot')
                    .classList.add('running');
            }

            if (action === 'stop') {

                sessionIsActive = false;

                document.getElementById('status-text').textContent =
                    'Mesin tidak aktif';

                document.getElementById('status-dot')
                    .classList.remove('running');
            }

        })

        .catch(function (error) {

            showMessage(
                error.message || 'Terjadi kesalahan koneksi.',
                false
            );

        })

        .finally(function () {

            startButton.disabled = false;
            stopButton.disabled = false;

        });
    }

    // TOMBOL START
    document.getElementById('start-btn')
        .addEventListener('click', function () {
            send('start');
        });

    // TOMBOL STOP
    document.getElementById('stop-btn')
        .addEventListener('click', function () {
            send('stop');
        });

})();
</script>

@endpush
