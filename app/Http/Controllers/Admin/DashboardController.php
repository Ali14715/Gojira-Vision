<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Department;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $currentMonth = $today->month;
        $currentYear = $today->year;

        // Basic stats
        $totalKaryawan = User::where('role', 'karyawan')->count();
        $hadirHariIni = Attendance::where('date', $today)->count();
        $belumAbsen = $totalKaryawan - $hadirHariIni;
        $terlambat = Attendance::where('date', $today)->where('status', 'terlambat')->count();

        // Face registered stats
        $faceRegistered = User::where('role', 'karyawan')->where('face_registered', true)->count();
        $faceNotRegistered = $totalKaryawan - $faceRegistered;

        // Monthly attendance data (last 7 days)
        $weeklyData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = $today->copy()->subDays($i);
            $dayAttendances = Attendance::where('date', $date)->get();
            $weeklyData[] = [
                'date' => $date->translatedFormat('D, d/m'),
                'hadir' => $dayAttendances->where('status', 'hadir')->count(),
                'terlambat' => $dayAttendances->where('status', 'terlambat')->count(),
                'total_karyawan' => $totalKaryawan,
            ];
        }

        // Monthly summary
        $monthlyAttendances = Attendance::whereMonth('date', $currentMonth)
            ->whereYear('date', $currentYear)
            ->get();
        $monthlyHadir = $monthlyAttendances->where('status', 'hadir')->count();
        $monthlyTerlambat = $monthlyAttendances->where('status', 'terlambat')->count();
        $monthlyTotal = $monthlyAttendances->count();

        // Department stats
        $departments = Department::withCount('users')->get();

        // Attendance rate (percentage)
        $workingDays = $today->day; // simplified: days passed this month
        $expectedAttendances = $totalKaryawan * $workingDays;
        $attendanceRate = $expectedAttendances > 0 ? round(($monthlyTotal / $expectedAttendances) * 100) : 0;
        $onTimeRate = $monthlyTotal > 0 ? round(($monthlyHadir / $monthlyTotal) * 100) : 0;

        // Recent attendances
        $recentAttendances = Attendance::with('user')
            ->where('date', $today)
            ->latest('clock_in')
            ->take(10)
            ->get();

        // Clock-out stats for today
        $clockedOut = Attendance::where('date', $today)->whereNotNull('clock_out')->count();
        $stillWorking = $hadirHariIni - $clockedOut;

        // Overtime today
        $overtime = Attendance::where('date', $today)->where('clock_out_status', 'lembur')->count();

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'belumAbsen',
            'terlambat',
            'faceRegistered',
            'faceNotRegistered',
            'weeklyData',
            'monthlyHadir',
            'monthlyTerlambat',
            'monthlyTotal',
            'departments',
            'attendanceRate',
            'onTimeRate',
            'recentAttendances',
            'clockedOut',
            'stillWorking',
            'overtime'
        ));
    }
}
