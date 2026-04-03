<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\WorkSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $departments = Department::all();

        $query = User::where('role', 'karyawan')->with(['department', 'workSchedules']);

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $employees = $query->latest()->paginate(15)->appends($request->query());

        return view('admin.schedules.index', compact('employees', 'departments'));
    }

    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'employee_ids' => 'required|array|min:1',
            'employee_ids.*' => 'exists:users,id',
            'schedules' => 'required|array|size:7',
            'schedules.*.day_of_week' => 'required|integer|between:1,7',
            'schedules.*.is_day_off' => 'required',
            'schedules.*.clock_in_time' => 'nullable|date_format:H:i',
            'schedules.*.clock_out_time' => 'nullable|date_format:H:i',
        ]);

        $employeeIds = $request->employee_ids;
        $schedules = $request->schedules;

        DB::transaction(function () use ($employeeIds, $schedules) {
            foreach ($employeeIds as $employeeId) {
                foreach ($schedules as $schedule) {
                    $isDayOff = filter_var($schedule['is_day_off'], FILTER_VALIDATE_BOOLEAN);

                    WorkSchedule::updateOrCreate(
                        [
                            'user_id' => $employeeId,
                            'day_of_week' => $schedule['day_of_week'],
                        ],
                        [
                            'clock_in_time' => $isDayOff ? null : $schedule['clock_in_time'],
                            'clock_out_time' => $isDayOff ? null : $schedule['clock_out_time'],
                            'is_day_off' => $isDayOff,
                        ]
                    );
                }
            }
        });

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal berhasil diterapkan untuk ' . count($employeeIds) . ' karyawan.');
    }
}
