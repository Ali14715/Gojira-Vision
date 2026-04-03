@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.attendances.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="date" class="form-label mb-0">Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="date" name="date" value="{{ $date }}">
            </div>
            <div class="col-auto">
                <label for="search" class="form-label mb-0">Cari Nama</label>
                <input type="text" class="form-control form-control-sm" id="search" name="search"
                       value="{{ $search }}" placeholder="Nama karyawan...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> Cari
                </button>
            </div>
            <div class="col-auto ms-auto">
                <a href="{{ route('admin.attendances.export', ['date' => $date, 'search' => $search]) }}" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                </a>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $att)
                        <tr>
                            <td>{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-avatar" style="width: 28px; height: 28px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                    </div>
                                    {{ $att->user->name }}
                                </div>
                            </td>
                            <td><span style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">{{ $att->date->format('d/m/Y') }}</span></td>
                            <td><span style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">{{ $att->clock_in ?? '-' }}</span></td>
                            <td><span style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">{{ $att->clock_out ?? '-' }}</span></td>
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
                            <td colspan="7" class="text-center py-4" style="color: var(--gv-text-dim);">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Tidak ada data absensi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $attendances->links() }}
</div>
@endsection
