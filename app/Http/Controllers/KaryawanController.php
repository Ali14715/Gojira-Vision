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

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $monthlyAttendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $today->month)
            ->whereYear('date', $today->year)
            ->get();

        $totalHadir = $monthlyAttendances->count();
        $totalTerlambat = $monthlyAttendances->where('status', 'terlambat')->count();

        return view('karyawan.dashboard', compact(
            'todayAttendance',
            'totalHadir',
            'totalTerlambat'
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
