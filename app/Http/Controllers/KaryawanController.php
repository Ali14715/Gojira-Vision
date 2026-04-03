<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class KaryawanController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();

        $totalHadir = $monthlyAttendances->count();
        $totalTerlambat = $monthlyAttendances->where('status', 'terlambat')->count();
        $totalTepat = $monthlyAttendances->where('status', 'hadir')->count();

        // Weekly data (last 7 days)
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $att = $monthlyAttendances->firstWhere('date', $date->toDateString());
            $weeklyData[] = [
                'date' => $date->translatedFormat('D'),
                'full_date' => $date->translatedFormat('d/m'),
                'status' => $att ? $att->status : 'alpha',
                'clock_in' => $att ? $att->clock_in : null,
                'clock_out' => $att ? $att->clock_out : null,
            ];
        }

        // Work schedule info
        $todaySchedule = $user->getTodaySchedule();

        // Overtime count this month
        $overtimeCount = $monthlyAttendances->where('clock_out_status', 'lembur')->count();

        // Early leave count
        $earlyLeave = $monthlyAttendances->where('clock_out_status', 'pulang_cepat')->count();

        // Working days this month (simplified)
        $workingDays = $today->day;
        $attendanceRate = $workingDays > 0 ? round(($totalHadir / $workingDays) * 100) : 0;
        $attendanceRate = min($attendanceRate, 100);

        return view('karyawan.dashboard', compact(
            'todayAttendance',
            'totalHadir',
            'totalTerlambat',
            'totalTepat',
            'weeklyData',
            'todaySchedule',
            'overtimeCount',
            'earlyLeave',
            'attendanceRate'
        ));
    }

    public function history()
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->latest('date')
            ->paginate(15);

        return view('karyawan.history', compact('attendances'));
    }
}
