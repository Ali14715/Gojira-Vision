<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KioskController extends Controller
{
    public function index()
    {
        return view('kiosk');
    }

    public function clockIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user_id;
        $today = Carbon::today();
        $now = Carbon::now();

        $existing = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if ($existing && $existing->clock_out) {
            return response()->json(['message' => 'Absensi hari ini sudah lengkap.', 'type' => 'done'], 422);
        }

        if ($existing && $existing->clock_in && !$existing->clock_out) {
            // Clock out
            $existing->update(['clock_out' => $now->toTimeString()]);
            return response()->json([
                'message' => 'Absen pulang berhasil!',
                'type' => 'clock_out',
                'time' => $now->format('H:i:s'),
            ]);
        }

        // Clock in
        // Cek jadwal karyawan — fallback ke 08:00 jika belum diatur
        $status = Attendance::determineStatus($userId, $now);

        Attendance::create([
            'user_id' => $userId,
            'date' => $today,
            'clock_in' => $now->toTimeString(),
            'status' => $status,
        ]);

        return response()->json([
            'message' => 'Absen masuk berhasil!',
            'type' => 'clock_in',
            'status' => $status,
            'time' => $now->format('H:i:s'),
        ]);
    }

    public function descriptors()
    {
        $descriptors = FaceDescriptor::with('user:id,name,position')->get()->map(function ($fd) {
            return [
                'user_id' => $fd->user_id,
                'name' => $fd->user->name,
                'position' => $fd->user->position,
                'descriptor' => $fd->descriptor,
            ];
        });

        return response()->json($descriptors);
    }
}
