<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'position',
        'department_id',
        'phone',
        'face_registered',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'face_registered' => 'boolean',
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isKaryawan(): bool
    {
        return $this->role === 'karyawan';
    }

    public function faceDescriptors()
    {
        return $this->hasMany(FaceDescriptor::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }

    public function getTodaySchedule(): ?WorkSchedule
    {
        return $this->workSchedules()->where('day_of_week', now()->dayOfWeekIso)->first();
    }

    public function getScheduleSummaryAttribute(): string
    {
        $schedules = $this->workSchedules->sortBy('day_of_week');

        if ($schedules->isEmpty()) {
            return 'Belum diatur';
        }

        $shortDays = [1 => 'Sen', 2 => 'Sel', 3 => 'Rab', 4 => 'Kam', 5 => 'Jum', 6 => 'Sab', 7 => 'Min'];
        $groups = [];
        $current = null;

        foreach ($schedules as $s) {
            $key = $s->is_day_off
                ? 'libur'
                : substr($s->clock_in_time, 0, 5) . '-' . substr($s->clock_out_time, 0, 5);

            if ($current && $current['key'] === $key && $current['last_day'] === $s->day_of_week - 1) {
                $current['last_day'] = $s->day_of_week;
                $current['end'] = $shortDays[$s->day_of_week];
            } else {
                if ($current) $groups[] = $current;
                $current = [
                    'key' => $key,
                    'start' => $shortDays[$s->day_of_week],
                    'end' => $shortDays[$s->day_of_week],
                    'last_day' => $s->day_of_week,
                ];
            }
        }
        if ($current) $groups[] = $current;

        $parts = [];
        foreach ($groups as $g) {
            $range = $g['start'] === $g['end'] ? $g['start'] : $g['start'] . '-' . $g['end'];
            if ($g['key'] === 'libur') {
                $parts[] = $range . ' Libur';
            } else {
                $parts[] = $range . ' ' . $g['key'];
            }
        }

        return implode(', ', $parts);
    }
}
