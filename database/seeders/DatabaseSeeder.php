<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Attendance;
use App\Models\WorkSchedule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ====== DEPARTMENTS ======
        $departments = [
            'Engineering',
            'Human Resources',
            'Marketing',
            'Finance',
            'Operations',
        ];

        $deptModels = [];
        foreach ($departments as $name) {
            $deptModels[$name] = Department::create(['name' => $name]);
        }

        // ====== ADMIN ======
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gojiravision.com',
            'password' => 'password',
            'role' => 'admin',
            'position' => 'System Admin',
            'department_id' => $deptModels['Engineering']->id,
        ]);

        // ====== KARYAWAN ======
        $employees = [
            ['name' => 'Budi Santoso',      'email' => 'budi@gojiravision.com',      'position' => 'Full Stack Developer', 'department' => 'Engineering',      'phone' => '081234567890', 'face' => true],
            ['name' => 'Siti Rahayu',        'email' => 'siti@gojiravision.com',      'position' => 'UI/UX Designer',      'department' => 'Engineering',      'phone' => '081234567891', 'face' => true],
            ['name' => 'Ahmad Hidayat',      'email' => 'ahmad@gojiravision.com',     'position' => 'Backend Developer',   'department' => 'Engineering',      'phone' => '081234567892', 'face' => true],
            ['name' => 'Dewi Lestari',       'email' => 'dewi@gojiravision.com',      'position' => 'HR Manager',          'department' => 'Human Resources',  'phone' => '081234567893', 'face' => true],
            ['name' => 'Rizky Pratama',      'email' => 'rizky@gojiravision.com',     'position' => 'HR Staff',            'department' => 'Human Resources',  'phone' => '081234567894', 'face' => true],
            ['name' => 'Nina Kusuma',        'email' => 'nina@gojiravision.com',      'position' => 'Marketing Lead',      'department' => 'Marketing',        'phone' => '081234567895', 'face' => true],
            ['name' => 'Fajar Nugroho',      'email' => 'fajar@gojiravision.com',     'position' => 'Content Strategist',  'department' => 'Marketing',        'phone' => '081234567896', 'face' => true],
            ['name' => 'Rina Wulandari',     'email' => 'rina@gojiravision.com',      'position' => 'Finance Manager',     'department' => 'Finance',          'phone' => '081234567897', 'face' => true],
            ['name' => 'Dimas Aditya',       'email' => 'dimas@gojiravision.com',     'position' => 'Accountant',          'department' => 'Finance',          'phone' => '081234567898', 'face' => false],
            ['name' => 'Maya Putri',         'email' => 'maya@gojiravision.com',      'position' => 'Operations Manager',  'department' => 'Operations',       'phone' => '081234567899', 'face' => true],
            ['name' => 'Eko Prasetyo',       'email' => 'eko@gojiravision.com',       'position' => 'DevOps Engineer',     'department' => 'Engineering',      'phone' => '081234567800', 'face' => true],
            ['name' => 'Anisa Fitriani',     'email' => 'anisa@gojiravision.com',     'position' => 'QA Engineer',         'department' => 'Engineering',      'phone' => '081234567801', 'face' => false],
        ];

        $userModels = [];
        $empId = 1;

        foreach ($employees as $emp) {
            $userModels[] = User::create([
                'name' => $emp['name'],
                'email' => $emp['email'],
                'password' => 'password',
                'role' => 'karyawan',
                'position' => $emp['position'],
                'department_id' => $deptModels[$emp['department']]->id,
                'phone' => $emp['phone'],
                'face_registered' => $emp['face'],
                'employee_id' => str_pad($empId++, 4, '0', STR_PAD_LEFT),
            ]);
        }

        // ====== WORK SCHEDULES ======
        // Standard schedule: Mon-Fri 08:00-17:00, Sat-Sun off
        // Some variation for realism
        $scheduleTypes = [
            'standard' => [
                1 => ['08:00', '17:00'], 2 => ['08:00', '17:00'], 3 => ['08:00', '17:00'],
                4 => ['08:00', '17:00'], 5 => ['08:00', '17:00'], 6 => 'off', 7 => 'off',
            ],
            'shift_a' => [
                1 => ['07:00', '16:00'], 2 => ['07:00', '16:00'], 3 => ['07:00', '16:00'],
                4 => ['07:00', '16:00'], 5 => ['07:00', '16:00'], 6 => ['07:00', '12:00'], 7 => 'off',
            ],
            'shift_b' => [
                1 => ['09:00', '18:00'], 2 => ['09:00', '18:00'], 3 => ['09:00', '18:00'],
                4 => ['09:00', '18:00'], 5 => ['09:00', '18:00'], 6 => 'off', 7 => 'off',
            ],
        ];

        // Assign schedule types to employees
        $scheduleAssign = [
            'standard', 'standard', 'shift_b', 'standard', 'standard',
            'shift_a', 'standard', 'standard', 'standard', 'shift_a',
            'shift_b', 'standard',
        ];

        foreach ($userModels as $i => $user) {
            $type = $scheduleTypes[$scheduleAssign[$i]];
            foreach ($type as $day => $times) {
                if ($times === 'off') {
                    WorkSchedule::create([
                        'user_id' => $user->id,
                        'day_of_week' => $day,
                        'is_day_off' => true,
                    ]);
                } else {
                    WorkSchedule::create([
                        'user_id' => $user->id,
                        'day_of_week' => $day,
                        'clock_in_time' => $times[0],
                        'clock_out_time' => $times[1],
                    ]);
                }
            }
        }

        // ====== ATTENDANCE (last 14 days) ======
        $today = Carbon::today();

        foreach ($userModels as $i => $user) {
            $type = $scheduleTypes[$scheduleAssign[$i]];

            for ($d = 13; $d >= 0; $d--) {
                $date = $today->copy()->subDays($d);
                $dayOfWeek = $date->dayOfWeekIso; // 1=Mon .. 7=Sun

                $schedule = $type[$dayOfWeek] ?? 'off';
                if ($schedule === 'off') continue;

                // Skip some days randomly for realism (simulate absence / not yet)
                if ($date->eq($today) && $d === 0) {
                    // Today: ~70% chance they've clocked in already
                    if (rand(1, 10) > 7) continue;
                }

                // Random skip ~5% of past days
                if ($d > 0 && rand(1, 100) <= 5) continue;

                $scheduledIn = $schedule[0];
                $scheduledOut = $schedule[1];

                // Clock in: mostly on time, some late
                $lateChance = rand(1, 100);
                if ($lateChance <= 15) {
                    // Late: 5-45 min late
                    $minutesLate = rand(5, 45);
                    $clockIn = Carbon::parse($scheduledIn)->addMinutes($minutesLate)->format('H:i:s');
                    $status = 'terlambat';
                } elseif ($lateChance <= 30) {
                    // Early: 5-20 min early
                    $minutesEarly = rand(5, 20);
                    $clockIn = Carbon::parse($scheduledIn)->subMinutes($minutesEarly)->format('H:i:s');
                    $status = 'hadir';
                } else {
                    // On time: -3 to +3 min
                    $drift = rand(-3, 3);
                    $clockIn = Carbon::parse($scheduledIn)->addMinutes($drift)->format('H:i:s');
                    $status = 'hadir';
                }

                // Clock out: only for past days (not today)
                $clockOut = null;
                $clockOutStatus = null;

                if ($d > 0) {
                    $outChance = rand(1, 100);
                    if ($outChance <= 10) {
                        // Early leave: 15-60 min early
                        $minutesEarly = rand(15, 60);
                        $clockOut = Carbon::parse($scheduledOut)->subMinutes($minutesEarly)->format('H:i:s');
                        $clockOutStatus = 'pulang_cepat';
                    } elseif ($outChance <= 25) {
                        // Overtime: 30-120 min late
                        $minutesOver = rand(30, 120);
                        $clockOut = Carbon::parse($scheduledOut)->addMinutes($minutesOver)->format('H:i:s');
                        $clockOutStatus = 'lembur';
                    } else {
                        // Normal: 0-15 min after scheduled
                        $drift = rand(0, 15);
                        $clockOut = Carbon::parse($scheduledOut)->addMinutes($drift)->format('H:i:s');
                        $clockOutStatus = 'tepat_waktu';
                    }
                } else {
                    // Today: some have clocked out, some haven't
                    if (rand(1, 10) <= 3) {
                        $clockOut = Carbon::parse($scheduledOut)->addMinutes(rand(0, 10))->format('H:i:s');
                        $clockOutStatus = 'tepat_waktu';
                    }
                }

                Attendance::create([
                    'user_id' => $user->id,
                    'date' => $date->toDateString(),
                    'clock_in' => $clockIn,
                    'clock_out' => $clockOut,
                    'status' => $status,
                    'clock_out_status' => $clockOutStatus,
                ]);
            }
        }
    }
}
