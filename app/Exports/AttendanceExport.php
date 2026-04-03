<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AttendanceExport implements FromQuery, WithHeadings, WithMapping, WithStyles
{
    use Exportable;

    protected string $date;
    protected ?string $search;

    public function __construct(string $date, ?string $search = null)
    {
        $this->date = $date;
        $this->search = $search;
    }

    public function query()
    {
        $query = Attendance::with('user')->where('date', $this->date);

        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', "%{$this->search}%");
            });
        }

        return $query->latest('clock_in');
    }

    public function headings(): array
    {
        return ['No', 'Nama', 'Tanggal', 'Jam Masuk', 'Jam Pulang', 'Status', 'Keterangan'];
    }

    public function map($attendance): array
    {
        static $no = 0;
        $no++;

        $statusMap = [
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'alpha' => 'Alpha',
        ];

        $clockOutStatusMap = [
            'lembur' => 'Lembur',
            'pulang_cepat' => 'Pulang Cepat',
            'tepat_waktu' => 'Tepat Waktu',
        ];

        return [
            $no,
            $attendance->user->name ?? '-',
            Carbon::parse($attendance->date)->format('d/m/Y'),
            $attendance->clock_in ?? '-',
            $attendance->clock_out ?? '-',
            $statusMap[$attendance->status] ?? $attendance->status,
            $clockOutStatusMap[$attendance->clock_out_status] ?? '-',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
