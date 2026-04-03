<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        return view('karyawan.attendance', compact('todayAttendance'));
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

        if ($existing) {
            return response()->json(['message' => 'Anda sudah absen masuk hari ini.'], 422);
        }

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
            'status' => $status,
            'time' => $now->format('H:i:s'),
        ]);
    }

    public function clockOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $userId = $request->user_id;
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $userId)
            ->where('date', $today)
            ->first();

        if (!$attendance) {
            return response()->json(['message' => 'Anda belum absen masuk hari ini.'], 422);
        }

        if ($attendance->clock_out) {
            return response()->json(['message' => 'Anda sudah absen pulang hari ini.'], 422);
        }

        $attendance->update([
            'clock_out' => Carbon::now()->toTimeString(),
            'clock_out_status' => Attendance::determineClockOutStatus($userId, Carbon::now()),
        ]);

        return response()->json([
            'message' => 'Absen pulang berhasil!',
            'time' => Carbon::now()->format('H:i:s'),
            'clock_out_status' => $attendance->fresh()->clock_out_status,
        ]);
    }
}
