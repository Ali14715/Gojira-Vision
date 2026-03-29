@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalKaryawan }}</div>
                    <div class="stat-label">Total Karyawan</div>
                </div>
                <i class="bi bi-people-fill stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $hadirHariIni }}</div>
                    <div class="stat-label">Hadir Hari Ini</div>
                </div>
                <i class="bi bi-check-circle-fill stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $terlambat }}</div>
                    <div class="stat-label">Terlambat</div>
                </div>
                <i class="bi bi-clock-fill stat-icon"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $belumAbsen }}</div>
                    <div class="stat-label">Belum Absen</div>
                </div>
                <i class="bi bi-x-circle-fill stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0"><i class="bi bi-activity me-2" style="color: var(--gv-green);"></i>Absensi Hari Ini</h6>
        <a href="{{ route('admin.attendances.index') }}" class="btn btn-outline-secondary btn-sm">
            Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
        </a>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $att)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                         style="width: 30px; height: 30px; background: var(--gv-green-glow); color: var(--gv-green); font-weight: 700; font-size: 0.75rem;">
                                        {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                    </div>
                                    {{ $att->user->name }}
                                </div>
                            </td>
                            <td>{{ $att->clock_in ?? '-' }}</td>
                            <td>{{ $att->clock_out ?? '-' }}</td>
                            <td>
                                @if($att->status === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($att->status === 'terlambat')
                                    <span class="badge bg-warning">Terlambat</span>
                                @else
                                    <span class="badge bg-danger">Alpha</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-4" style="color: var(--gv-text-dim);">Belum ada yang absen hari ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
