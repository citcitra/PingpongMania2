<?php

namespace App\Http\Controllers;

use App\Models\TrainingSession;
use App\Services\RaspberryPiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use App\WaitingList;
use App\Mail\WaitingListApprovedMail;
use Illuminate\Support\Facades\Mail;

class PingPongController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ==================================================
    // PEMERIKSAAN HAK AKSES PENGGUNA
    // ==================================================

    private function ensureUserCanControl()
    {
        if (auth()->user()->role !== 'user') {
            abort(403, 'Hanya pengguna yang dapat mengontrol mesin.');
        }
    }

    // ==================================================
    // DASHBOARD
    // ==================================================

    public function dashboard()
    {
        $userId = auth()->id();

        $sessions = TrainingSession::where('user_id', $userId)
            ->latest()
            ->limit(8)
            ->get();

        $totalSessions = TrainingSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->count();

        $averageScore = TrainingSession::where('user_id', $userId)
            ->whereNotNull('score')
            ->avg('score');

        $totalSeconds = (int) TrainingSession::where('user_id', $userId)
            ->where('status', 'completed')
            ->sum('duration_seconds');

        $activeSession = TrainingSession::where('status', 'running')
            ->latest()
            ->first();

        $slotId = 1;

        $slotPenuh = false;

        if (
            $activeSession &&
            (int) $activeSession->user_id !== (int) $userId
        ) {
            $slotPenuh = true;
        }

        return view('pingpong.dashboard', compact(
            'sessions',
            'totalSessions',
            'averageScore',
            'totalSeconds',
            'activeSession',
            'slotId',
            'slotPenuh'
        ));
    }

    // ==================================================
    // STOP LATIHAN
    // ==================================================

    public function stop(RaspberryPiClient $raspberry)
    {
        $this->ensureUserCanControl();

        $userId = auth()->id();

        $active = TrainingSession::where('status', 'running')
            ->latest()
            ->first();

        if (
            $active &&
            (int) $active->user_id !== (int) $userId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Mesin sedang digunakan oleh pengguna lain.'
            ], 409);
        }

        try {
            $reply = $raspberry->send([
                'action' => 'stop',
                'request_id' => bin2hex(random_bytes(16)),
            ]);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 502);
        }

        $slotId = 1;

        if ($active) {
            $endedAt = Carbon::now();

            $active->ended_at = $endedAt;

            $active->duration_seconds = max(
                0,
                $active->started_at
                    ? $active->started_at->diffInSeconds($endedAt)
                    : 0
            );

            $active->status = 'completed';
            $active->save();

            // PROSES ANTREAN BERIKUTNYA

            $nextQueue = WaitingList::where('slot_id', $slotId)
                ->where('status', 'waiting')
                ->where('position', 1)
                ->first();

            if ($nextQueue) {
                $nextQueue->update([
                    'status' => 'approved',
                    'position' => 0
                ]);

                try {
                    Mail::to($nextQueue->user->email)
                        ->send(
                            new WaitingListApprovedMail($nextQueue->user)
                        );
                } catch (\Exception $mailException) {
                    // Kegagalan email tidak membatalkan STOP.
                }

                $remainingQueues = WaitingList::where('slot_id', $slotId)
                    ->where('status', 'waiting')
                    ->orderBy('position', 'asc')
                    ->get();

                foreach ($remainingQueues as $queue) {
                    $queue->decrement('position');
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Latihan berhasil dihentikan.',
            'device' => $reply
        ]);
    }

    // ==================================================
    // RIWAYAT LATIHAN
    // ==================================================

    public function history(Request $request)
    {
        $query = TrainingSession::where('user_id', auth()->id())
            ->latest();

        if ($request->filled('mode')) {
            $query->where(
                'mode',
                $request->input('mode')
            );
        }

        if ($request->filled('date')) {
            $query->whereDate(
                'created_at',
                $request->input('date')
            );
        }

        $sessions = $query->paginate(15)
            ->appends($request->only('mode', 'date'));

        return view('pingpong.history', compact('sessions'));
    }

    // ==================================================
    // START DAN PERBARUI PENGATURAN
    // ==================================================

    public function control(
        Request $request,
        RaspberryPiClient $raspberry
    ) {
        $this->ensureUserCanControl();

        // NORMALISASI INPUT

        $mode = strtolower(trim(
            (string) $request->input('mode')
        ));

        if ($mode === 'sequence') {
            $request->merge([
                'direction' => 'sequence'
            ]);
        } elseif ($mode === 'random') {
            $request->merge([
                'direction' => 'random'
            ]);
        } else {
            $request->merge([
                'direction' => strtolower(trim(
                    (string) $request->input('direction')
                ))
            ]);
        }

        // NORMALISASI URUTAN SEQUENCE

        if ($mode === 'sequence') {
            $rawSequence = (string) $request->input(
                'sequence',
                ''
            );

            $directions = array_map(function ($item) {
                $item = strtolower(trim($item));

                $mapping = [
                    'kiri' => 'left',
                    'left' => 'left',
                    'tengah' => 'center',
                    'center' => 'center',
                    'kanan' => 'right',
                    'right' => 'right',
                ];

                return isset($mapping[$item])
                    ? $mapping[$item]
                    : $item;
            }, explode(',', $rawSequence));

            $request->merge([
                'sequence' => implode(',', $directions)
            ]);
        }

        // VALIDASI INPUT

        $data = $request->validate([
            'action' => 'required|in:start',
            'mode' => 'required|in:fixed,sequence,random',
            'speed' => 'required|in:low,medium,high',
            'interval_seconds' => 'required|integer|min:1|max:5',
            'direction' => 'required|in:left,center,right,random,sequence',
            'sequence' => 'nullable|string|max:500',
        ]);

        $sequenceString = null;

        // VALIDASI FIXED MODE

        if ($data['mode'] === 'fixed') {
            if (!in_array(
                $data['direction'],
                ['left', 'center', 'right'],
                true
            )) {
                return response()->json([
                    'success' => false,
                    'message' => 'Arah Fixed Mode tidak valid.'
                ], 422);
            }
        }

        // VALIDASI SEQUENCE MODE

        elseif ($data['mode'] === 'sequence') {
            if (empty($data['sequence'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Urutan Sequence Mode belum diisi.'
                ], 422);
            }

            $directions = array_map(
                'trim',
                explode(',', $data['sequence'])
            );

            if (
                count($directions) < 2 ||
                count($directions) > 20
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Urutan harus berisi minimal 2 dan maksimal 20 arah.'
                ], 422);
            }

            foreach ($directions as $direction) {
                if (!in_array(
                    $direction,
                    ['left', 'center', 'right'],
                    true
                )) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Urutan hanya boleh menggunakan kiri, tengah, dan kanan.'
                    ], 422);
                }
            }

            $sequenceString = implode(',', $directions);
        }

        // VALIDASI RANDOM MODE

        elseif ($data['mode'] === 'random') {
            if ($data['direction'] !== 'random') {
                return response()->json([
                    'success' => false,
                    'message' => 'Arah Random Mode tidak valid.'
                ], 422);
            }
        }

        $userId = auth()->id();

        // PERIKSA SESI DATABASE

        $globalActive = TrainingSession::where('status', 'running')
            ->latest()
            ->first();

        if (
            $globalActive &&
            (int) $globalActive->user_id !== (int) $userId
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Mesin sedang digunakan oleh pengguna lain. Silakan tunggu sampai sesi selesai.'
            ], 409);
        }

        // PERIKSA STATUS PICO WH

        try {
            $deviceStatus = $raspberry->status();
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal memeriksa status Pico WH: ' . $e->getMessage()
            ], 502);
        }

        $deviceRunning = (
            isset($deviceStatus['status']) &&
            strtoupper($deviceStatus['status']) === 'RUNNING'
        );

        if ($deviceRunning && !$globalActive) {
            return response()->json([
                'success' => false,
                'message' => 'Pico WH sedang berjalan tetapi sesi latihan belum tercatat. Tekan STOP terlebih dahulu, kemudian mulai latihan kembali.'
            ], 409);
        }

        // TENTUKAN START ATAU CONFIGURE

        $isUpdating = $deviceRunning;

        $command = [
            'action' => $isUpdating
                ? 'configure'
                : 'start',

            'request_id' => bin2hex(random_bytes(16)),

            'mode' => $data['mode'],

            'speed' => $data['speed'],

            'interval_seconds' =>
                (int) $data['interval_seconds'],

            'direction' => $data['direction'],

            'sequence' => $sequenceString,
        ];

        // KIRIM PENGATURAN KE PICO WH

        try {
            $deviceReply = $raspberry->send($command);
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pengaturan ke Pico WH: ' . $e->getMessage()
            ], 502);
        }

        // SIMPAN ATAU PERBARUI SESI LATIHAN

        try {
            DB::transaction(function () use (
                $userId,
                $data,
                $sequenceString,
                $isUpdating
            ) {
                $active = TrainingSession::where('status', 'running')
                    ->latest()
                    ->lockForUpdate()
                    ->first();

                if (
                    $active &&
                    (int) $active->user_id !== (int) $userId
                ) {
                    throw new RuntimeException(
                        'Sesi sedang digunakan pengguna lain.'
                    );
                }

                if ($isUpdating) {
                    if (!$active) {
                        throw new RuntimeException(
                            'Sesi latihan aktif tidak ditemukan.'
                        );
                    }

                    $active->mode = $data['mode'];
                    $active->speed = $data['speed'];

                    $active->interval_seconds =
                        (int) $data['interval_seconds'];

                    $active->direction = $data['direction'];
                    $active->sequence = $sequenceString;

                    $active->save();
                } else {
                    if ($active) {
                        $endedAt = Carbon::now();

                        $active->ended_at = $endedAt;

                        $active->duration_seconds = max(
                            0,
                            $active->started_at
                                ? $active->started_at->diffInSeconds($endedAt)
                                : 0
                        );

                        $active->status = 'completed';
                        $active->save();
                    }

                    TrainingSession::create([
                        'user_id' => $userId,
                        'mode' => $data['mode'],
                        'speed' => $data['speed'],

                        'interval_seconds' =>
                            (int) $data['interval_seconds'],

                        'direction' => $data['direction'],
                        'sequence' => $sequenceString,
                        'status' => 'running',
                        'started_at' => Carbon::now(),
                    ]);
                }
            });
        } catch (RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 409);
        }

        // RESPONSE

        return response()->json([
            'success' => true,

            'message' => $isUpdating
                ? 'Pengaturan berhasil diperbarui. Latihan tetap berjalan.'
                : 'Latihan berhasil dimulai.',

            'session_updated' => $isUpdating,

            'device' => $deviceReply,
        ]);
    }
}
