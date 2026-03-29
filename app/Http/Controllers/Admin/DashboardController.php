<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();

        $totalKaryawan = User::where('role', 'karyawan')->count();
        $hadirHariIni = Attendance::where('date', $today)->count();
        $belumAbsen = $totalKaryawan - $hadirHariIni;
        $terlambat = Attendance::where('date', $today)->where('status', 'terlambat')->count();

        $recentAttendances = Attendance::with('user')
            ->where('date', $today)
            ->latest('clock_in')
            ->take(10)
            ->get();

        return view('admin.dashboard', compact(
            'totalKaryawan',
            'hadirHariIni',
            'belumAbsen',
            'terlambat',
            'recentAttendances'
        ));
    }
}
