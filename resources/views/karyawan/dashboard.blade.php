@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
{{-- Today Status Card --}}
<div class="row g-3 mb-4">
    <div class="col-md-8 animate-in">
        <div class="card h-100">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-4">
                    @if($todayAttendance)
                        <div class="text-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; background: var(--gv-green-glow); border: 3px solid var(--gv-green);">
                                <i class="bi bi-check-lg" style="font-size: 2.5rem; color: var(--gv-green);"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1" style="color: var(--gv-green);">Sudah Absen Hari Ini</h5>
                            <div class="d-flex gap-4 mt-2">
                                <div>
                                    <div class="mini-stat-label">Masuk</div>
                                    <div class="mini-stat-value" style="font-size: 1.3rem;">{{ $todayAttendance->clock_in }}</div>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Pulang</div>
                                    <div class="mini-stat-value" style="font-size: 1.3rem;">{{ $todayAttendance->clock_out ?? '—' }}</div>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Status</div>
                                    @if($todayAttendance->status === 'hadir')
                                        <span class="badge bg-success mt-1">Tepat Waktu</span>
                                    @else
                                        <span class="badge bg-warning mt-1">Terlambat</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto" style="width: 80px; height: 80px; background: rgba(239,68,68,0.1); border: 3px solid var(--gv-danger);">
                                <i class="bi bi-x-lg" style="font-size: 2.5rem; color: var(--gv-danger);"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="mb-1" style="color: var(--gv-danger);">Belum Absen Hari Ini</h5>
                            <p class="mb-2" style="color: var(--gv-text-dim); font-size: 0.9rem;">
                                @if($todaySchedule)
                                    Jadwal masuk: <strong style="color: var(--gv-text);">{{ $todaySchedule->clock_in_time }}</strong>
                                @else
                                    Segera lakukan absensi melalui menu Absensi.
                                @endif
                            </p>
                            <a href="{{ route('karyawan.attendance') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-camera-video-fill me-1"></i> Absen Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 animate-in">
        <div class="card h-100">
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <div class="text-center">
                    <div class="mini-stat-label mb-1">Tingkat Kehadiran Bulan Ini</div>
                    <div style="font-family: 'JetBrains Mono', monospace; font-size: 3rem; font-weight: 700; color: {{ $attendanceRate >= 80 ? 'var(--gv-green)' : ($attendanceRate >= 50 ? 'var(--gv-warning)' : 'var(--gv-danger)') }};">
                        {{ $attendanceRate }}%
                    </div>
                    <div class="neo-progress mt-2" style="height: 10px;">
                        <div class="neo-progress-bar {{ $attendanceRate < 50 ? 'danger' : ($attendanceRate < 80 ? 'warning' : '') }}" style="width: {{ $attendanceRate }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 animate-in">
        <div class="stat-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalHadir }}</div>
                    <div class="stat-label">Hadir Bulan Ini</div>
                </div>
                <i class="bi bi-check-circle-fill stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 animate-in">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalTerlambat }}</div>
                    <div class="stat-label">Terlambat</div>
                </div>
                <i class="bi bi-clock-fill stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 animate-in">
        <div class="stat-card info">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $overtimeCount }}</div>
                    <div class="stat-label">Lembur</div>
                </div>
                <i class="bi bi-moon-stars-fill stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3 animate-in">
        <div class="stat-card danger">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $earlyLeave }}</div>
                    <div class="stat-label">Pulang Cepat</div>
                </div>
                <i class="bi bi-box-arrow-right stat-icon"></i>
            </div>
        </div>
    </div>
</div>

{{-- Weekly Timeline + Schedule --}}
<div class="row g-3 mb-4">
    <div class="col-md-8 animate-in">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-calendar-week me-2" style="color: var(--gv-green);"></i>7 Hari Terakhir</h6>
                <span class="neo-tag green">Aktivitas</span>
            </div>
            <div class="card-body">
                <div class="row g-2">
                    @foreach($weeklyData as $day)
                        <div class="col">
                            <div class="text-center p-2 rounded" style="border: 2px solid var(--gv-border); min-height: 110px;">
                                <div class="mini-stat-label mb-2">{{ $day['date'] }}</div>
                                <div class="mini-stat-label mb-2" style="font-size: 0.65rem;">{{ $day['full_date'] }}</div>
                                @if($day['status'] === 'hadir')
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 32px; height: 32px; background: var(--gv-green-glow); border: 2px solid var(--gv-green);">
                                        <i class="bi bi-check" style="color: var(--gv-green); font-size: 1rem;"></i>
                                    </div>
                                    <div style="font-size: 0.65rem; color: var(--gv-green); font-weight: 600;">Hadir</div>
                                @elseif($day['status'] === 'terlambat')
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 32px; height: 32px; background: rgba(245,158,11,0.1); border: 2px solid var(--gv-warning);">
                                        <i class="bi bi-clock" style="color: var(--gv-warning); font-size: 0.85rem;"></i>
                                    </div>
                                    <div style="font-size: 0.65rem; color: var(--gv-warning); font-weight: 600;">Terlambat</div>
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-1" style="width: 32px; height: 32px; background: rgba(113,113,122,0.1); border: 2px solid var(--gv-text-dim);">
                                        <i class="bi bi-dash" style="color: var(--gv-text-dim); font-size: 1rem;"></i>
                                    </div>
                                    <div style="font-size: 0.65rem; color: var(--gv-text-dim); font-weight: 600;">Alpha</div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 animate-in">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-clock me-2" style="color: var(--gv-info);"></i>Jadwal Hari Ini</h6>
            </div>
            <div class="card-body d-flex flex-column justify-content-center">
                @if($todaySchedule && !$todaySchedule->is_day_off)
                    <div class="text-center mb-3">
                        <div class="mini-stat-label mb-1">Jam Masuk</div>
                        <div style="font-family: 'JetBrains Mono', monospace; font-size: 2rem; font-weight: 700; color: var(--gv-green);">
                            {{ $todaySchedule->clock_in_time }}
                        </div>
                    </div>
                    <div class="neo-divider">sampai</div>
                    <div class="text-center mt-1">
                        <div class="mini-stat-label mb-1">Jam Pulang</div>
                        <div style="font-family: 'JetBrains Mono', monospace; font-size: 2rem; font-weight: 700; color: var(--gv-info);">
                            {{ $todaySchedule->clock_out_time }}
                        </div>
                    </div>
                @elseif($todaySchedule && $todaySchedule->is_day_off)
                    <div class="text-center py-3">
                        <i class="bi bi-emoji-sunglasses" style="font-size: 3rem; color: var(--gv-warning);"></i>
                        <h6 class="mt-2" style="color: var(--gv-warning);">Hari Libur</h6>
                        <p class="mb-0 mini-stat-label">Nikmati harimu!</p>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="bi bi-calendar-x" style="font-size: 3rem; color: var(--gv-text-dim);"></i>
                        <p class="mt-2 mb-0 mini-stat-label">Jadwal belum diatur.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!auth()->user()->face_registered)
    <div class="alert alert-warning animate-in">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Perhatian!</strong> Anda belum mendaftarkan wajah.
        <a href="{{ route('karyawan.face.register') }}" class="alert-link">Daftar wajah sekarang</a> untuk bisa menggunakan absensi.
    </div>
@endif
@endsection
