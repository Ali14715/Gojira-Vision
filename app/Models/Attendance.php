<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'clock_out_status',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function determineStatus(int $userId, Carbon $now): string
    {
        $user = User::find($userId);
        $schedule = $user?->getTodaySchedule();

        if ($schedule && $schedule->is_day_off) {
            return 'hadir';
        }

        if ($schedule && $schedule->clock_in_time) {
            $scheduledTime = Carbon::parse($schedule->clock_in_time);
            return $now->format('H:i:s') > $scheduledTime->format('H:i:s') ? 'terlambat' : 'hadir';
        }

        // Fallback: default 08:00
        $default = Carbon::today()->setHour(8)->setMinute(0)->setSecond(0);
        return $now->gt($default) ? 'terlambat' : 'hadir';
    }

    /**
     * Determine clock-out status based on schedule.
     * - pulang_cepat: pulang sebelum jadwal
     * - tepat_waktu: pulang sesuai/setelah jadwal
     * - lembur: pulang >= 3 jam setelah jadwal pulang
     */
    public static function determineClockOutStatus(int $userId, Carbon $now): string
    {
        $user = User::find($userId);
        $schedule = $user?->getTodaySchedule();

        if (!$schedule || $schedule->is_day_off || !$schedule->clock_out_time) {
            return 'tepat_waktu';
        }

        $scheduledOut = Carbon::parse($schedule->clock_out_time);
        $nowTime = $now->format('H:i:s');
        $scheduledTime = $scheduledOut->format('H:i:s');

        // Lembur: 3+ jam setelah jadwal pulang
        $overtimeThreshold = $scheduledOut->copy()->addHours(3)->format('H:i:s');
        if ($nowTime >= $overtimeThreshold) {
            return 'lembur';
        }

        // Pulang cepat: sebelum jadwal
        if ($nowTime < $scheduledTime) {
            return 'pulang_cepat';
        }

        return 'tepat_waktu';
    }
}
