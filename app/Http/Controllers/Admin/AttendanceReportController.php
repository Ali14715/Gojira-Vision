<?php

namespace App\Http\Controllers\Admin;

use App\Exports\AttendanceExport;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class AttendanceReportController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $search = $request->input('search');

        $query = Attendance::with('user')->where('date', $date);

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $attendances = $query->latest('clock_in')->paginate(15);
        $attendances->appends($request->query());

        return view('admin.attendances.index', compact('attendances', 'date', 'search'));
    }

    public function export(Request $request)
    {
        $date = $request->input('date', Carbon::today()->toDateString());
        $search = $request->input('search');

        $filename = 'absensi_' . $date . '.xlsx';

        return Excel::download(new AttendanceExport($date, $search), $filename);
    }
}
