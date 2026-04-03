@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
{{-- Main Stats --}}
<div class="row g-3 mb-4">
    <div class="col-md-3 animate-in">
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

    <div class="col-md-3 animate-in">
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

    <div class="col-md-3 animate-in">
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

    <div class="col-md-3 animate-in">
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

{{-- Charts Row --}}
<div class="row g-3 mb-4">
    {{-- Weekly Attendance Chart --}}
    <div class="col-md-8 animate-in">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-bar-chart-fill me-2" style="color: var(--gv-green);"></i>Absensi 7 Hari Terakhir</h6>
                <span class="neo-tag green">Weekly</span>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height: 260px;">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Stats --}}
    <div class="col-md-4 animate-in">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-lightning-fill me-2" style="color: var(--gv-warning);"></i>Status Hari Ini</h6>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background: var(--gv-green-glow); color: var(--gv-green);">
                        <i class="bi bi-person-check-fill"></i>
                    </div>
                    <div>
                        <div class="mini-stat-value">{{ $clockedOut }}</div>
                        <div class="mini-stat-label">Sudah Pulang</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background: rgba(59,130,246,0.12); color: var(--gv-info);">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <div>
                        <div class="mini-stat-value">{{ $stillWorking }}</div>
                        <div class="mini-stat-label">Masih Bekerja</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background: rgba(245,158,11,0.12); color: var(--gv-warning);">
                        <i class="bi bi-moon-stars-fill"></i>
                    </div>
                    <div>
                        <div class="mini-stat-value">{{ $overtime }}</div>
                        <div class="mini-stat-label">Lembur</div>
                    </div>
                </div>

                <div class="neo-divider">Bulan Ini</div>

                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="mini-stat-label">Tingkat Kehadiran</span>
                        <span class="mini-stat-label" style="color: var(--gv-green);">{{ $attendanceRate }}%</span>
                    </div>
                    <div class="neo-progress">
                        <div class="neo-progress-bar" style="width: {{ $attendanceRate }}%;"></div>
                    </div>
                </div>
                <div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="mini-stat-label">Tepat Waktu</span>
                        <span class="mini-stat-label" style="color: var(--gv-green);">{{ $onTimeRate }}%</span>
                    </div>
                    <div class="neo-progress">
                        <div class="neo-progress-bar {{ $onTimeRate < 50 ? 'danger' : ($onTimeRate < 75 ? 'warning' : '') }}" style="width: {{ $onTimeRate }}%;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Department Stats + Face Registration --}}
<div class="row g-3 mb-4">
    <div class="col-md-5 animate-in">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-building me-2" style="color: var(--gv-info);"></i>Karyawan per Departemen</h6>
            </div>
            <div class="card-body">
                @if($departments->count() > 0)
                    <div class="chart-container" style="height: 220px;">
                        <canvas id="deptChart"></canvas>
                    </div>
                @else
                    <div class="text-center py-4" style="color: var(--gv-text-dim);">
                        <i class="bi bi-building" style="font-size: 2rem;"></i>
                        <p class="mt-2 mb-0">Belum ada departemen.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-3 animate-in">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-person-bounding-box me-2" style="color: var(--gv-green);"></i>Registrasi Wajah</h6>
            </div>
            <div class="card-body d-flex flex-column align-items-center justify-content-center">
                <div class="chart-container" style="height: 180px;">
                    <canvas id="faceChart"></canvas>
                </div>
                <div class="d-flex gap-3 mt-2">
                    <div class="text-center">
                        <div class="mini-stat-value" style="color: var(--gv-green);">{{ $faceRegistered }}</div>
                        <div class="mini-stat-label">Terdaftar</div>
                    </div>
                    <div class="text-center">
                        <div class="mini-stat-value" style="color: var(--gv-danger);">{{ $faceNotRegistered }}</div>
                        <div class="mini-stat-label">Belum</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4 animate-in">
        <div class="card h-100">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-calendar-check me-2" style="color: var(--gv-green);"></i>Ringkasan Bulan Ini</h6>
            </div>
            <div class="card-body d-flex flex-column gap-3">
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background: var(--gv-green-glow); color: var(--gv-green);">
                        <i class="bi bi-check2-all"></i>
                    </div>
                    <div>
                        <div class="mini-stat-value">{{ $monthlyHadir }}</div>
                        <div class="mini-stat-label">Total Hadir Tepat</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background: rgba(245,158,11,0.12); color: var(--gv-warning);">
                        <i class="bi bi-clock-history"></i>
                    </div>
                    <div>
                        <div class="mini-stat-value">{{ $monthlyTerlambat }}</div>
                        <div class="mini-stat-label">Total Terlambat</div>
                    </div>
                </div>
                <div class="mini-stat">
                    <div class="mini-stat-icon" style="background: rgba(59,130,246,0.12); color: var(--gv-info);">
                        <i class="bi bi-graph-up-arrow"></i>
                    </div>
                    <div>
                        <div class="mini-stat-value">{{ $monthlyTotal }}</div>
                        <div class="mini-stat-label">Total Absensi Masuk</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Attendance Table --}}
<div class="card animate-in">
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
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAttendances as $att)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-avatar" style="width: 30px; height: 30px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="fw-bold" style="font-size: 0.9rem;">{{ $att->user->name }}</div>
                                        <div style="font-size: 0.72rem; color: var(--gv-text-dim);">{{ $att->user->position ?? '-' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td><span class="font-mono" style="font-family: 'JetBrains Mono', monospace;">{{ $att->clock_in ?? '-' }}</span></td>
                            <td><span class="font-mono" style="font-family: 'JetBrains Mono', monospace;">{{ $att->clock_out ?? '-' }}</span></td>
                            <td>
                                @if($att->status === 'hadir')
                                    <span class="badge bg-success">Hadir</span>
                                @elseif($att->status === 'terlambat')
                                    <span class="badge bg-warning">Terlambat</span>
                                @else
                                    <span class="badge bg-danger">Alpha</span>
                                @endif
                            </td>
                            <td>
                                @if($att->clock_out_status === 'lembur')
                                    <span class="badge bg-info">Lembur</span>
                                @elseif($att->clock_out_status === 'pulang_cepat')
                                    <span class="badge bg-warning">Pulang Cepat</span>
                                @elseif($att->clock_out)
                                    <span class="badge bg-secondary">Selesai</span>
                                @else
                                    <span class="neo-tag">Bekerja</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color: var(--gv-text-dim);">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Belum ada yang absen hari ini.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.08)';
    const textColor = isDark ? '#a1a1aa' : '#555555';

    Chart.defaults.color = textColor;
    Chart.defaults.font.family = "'Space Grotesk', sans-serif";

    // Weekly Chart
    const weeklyData = @json($weeklyData);
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: weeklyData.map(d => d.date),
            datasets: [
                {
                    label: 'Hadir',
                    data: weeklyData.map(d => d.hadir),
                    backgroundColor: isDark ? 'rgba(0, 220, 130, 0.7)' : 'rgba(0, 168, 107, 0.8)',
                    borderColor: isDark ? '#00dc82' : '#1a1a1a',
                    borderWidth: 2,
                    borderRadius: 4,
                },
                {
                    label: 'Terlambat',
                    data: weeklyData.map(d => d.terlambat),
                    backgroundColor: isDark ? 'rgba(245, 158, 11, 0.7)' : 'rgba(217, 119, 6, 0.8)',
                    borderColor: isDark ? '#f59e0b' : '#1a1a1a',
                    borderWidth: 2,
                    borderRadius: 4,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        padding: 16,
                        font: { weight: '600', size: 11 }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { font: { weight: '600', size: 10 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { stepSize: 1, font: { weight: '600', size: 10 } }
                }
            }
        }
    });

    // Department Chart
    @if($departments->count() > 0)
    const deptData = @json($departments);
    const deptColors = [
        isDark ? '#00dc82' : '#00a86b',
        isDark ? '#3b82f6' : '#2563eb',
        isDark ? '#f59e0b' : '#d97706',
        isDark ? '#ef4444' : '#dc2626',
        isDark ? '#8b5cf6' : '#7c3aed',
        isDark ? '#ec4899' : '#db2777',
    ];
    new Chart(document.getElementById('deptChart'), {
        type: 'bar',
        data: {
            labels: deptData.map(d => d.name),
            datasets: [{
                label: 'Karyawan',
                data: deptData.map(d => d.users_count),
                backgroundColor: deptData.map((_, i) => deptColors[i % deptColors.length]),
                borderColor: isDark ? deptColors : '#1a1a1a',
                borderWidth: 2,
                borderRadius: 4,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: gridColor },
                    ticks: { stepSize: 1, font: { weight: '600', size: 10 } }
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { weight: '600', size: 11 } }
                }
            }
        }
    });
    @endif

    // Face Registration Doughnut
    new Chart(document.getElementById('faceChart'), {
        type: 'doughnut',
        data: {
            labels: ['Terdaftar', 'Belum'],
            datasets: [{
                data: [{{ $faceRegistered }}, {{ $faceNotRegistered }}],
                backgroundColor: [
                    isDark ? '#00dc82' : '#00a86b',
                    isDark ? 'rgba(239,68,68,0.5)' : 'rgba(220,38,38,0.5)',
                ],
                borderColor: isDark ? '#12121a' : '#1a1a1a',
                borderWidth: 3,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        padding: 12,
                        font: { weight: '600', size: 11 }
                    }
                }
            }
        }
    });
});
</script>
@endpush
