<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSchedule extends Model
{
    protected $fillable = [
        'user_id',
        'day_of_week',
        'clock_in_time',
        'clock_out_time',
        'is_day_off',
    ];

    protected function casts(): array
    {
        return [
            'is_day_off' => 'boolean',
        ];
    }

    const DAY_LABELS = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
