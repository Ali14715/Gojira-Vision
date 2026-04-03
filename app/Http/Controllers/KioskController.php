<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\FaceDescriptor;
use App\Models\User;
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
            'employee_id' => 'required|string',
            'mode' => 'nullable|in:clock-in,clock-out',
        ]);

        $user = User::where('employee_id', $request->employee_id)->first();

        if (!$user) {
            return response()->json(['message' => 'ID Karyawan tidak ditemukan.'], 404);
        }

        $mode = $request->input('mode', 'clock-in');
        $today = Carbon::today();
        $now = Carbon::now();

        $existing = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if ($mode === 'clock-out') {
            if (!$existing || !$existing->clock_in) {
                return response()->json(['message' => 'Belum absen masuk hari ini.', 'type' => 'error'], 422);
            }
            if ($existing->clock_out) {
                return response()->json(['message' => 'Sudah absen pulang hari ini.', 'type' => 'done'], 422);
            }

            $existing->update([
                'clock_out' => $now->toTimeString(),
                'clock_out_status' => Attendance::determineClockOutStatus($user->id, $now),
            ]);

            return response()->json([
                'message' => 'Absen pulang berhasil!',
                'type' => 'clock_out',
                'time' => $now->format('H:i:s'),
            ]);
        }

        // Mode: clock-in
        if ($existing) {
            return response()->json(['message' => 'Sudah absen masuk hari ini.', 'type' => 'done'], 422);
        }

        $status = Attendance::determineStatus($user->id, $now);

        Attendance::create([
            'user_id' => $user->id,
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
        $descriptors = FaceDescriptor::with('user:id,name,position,employee_id')->get()->map(function ($fd) {
            return [
                'user_id' => $fd->user_id,
                'employee_id' => $fd->user->employee_id,
                'name' => $fd->user->name,
                'position' => $fd->user->position,
                'descriptor' => $fd->descriptor,
            ];
        });

        return response()->json($descriptors);
    }

    /**
     * Lookup employee by employee_id — returns their face descriptors only.
     */
    public function lookup(Request $request)
    {
        $request->validate(['employee_id' => 'required|string']);

        $user = User::where('employee_id', $request->employee_id)->first();

        if (!$user) {
            return response()->json(['message' => 'ID Karyawan tidak ditemukan.'], 404);
        }

        if (!$user->face_registered) {
            return response()->json(['message' => 'Karyawan belum mendaftarkan wajah.'], 422);
        }

        $descriptors = $user->faceDescriptors->map(function ($fd) {
            return $fd->descriptor;
        });

        return response()->json([
            'user_id' => $user->id,
            'employee_id' => $user->employee_id,
            'name' => $user->name,
            'position' => $user->position,
            'descriptors' => $descriptors,
        ]);
    }
}
