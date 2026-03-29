@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
<div class="card">
    <div class="card-header">
        <form method="GET" action="{{ route('admin.attendances.index') }}" class="row g-2 align-items-end">
            <div class="col-auto">
                <label for="date" class="form-label mb-0 small">Tanggal</label>
                <input type="date" class="form-control form-control-sm" id="date" name="date" value="{{ $date }}">
            </div>
            <div class="col-auto">
                <label for="search" class="form-label mb-0 small">Cari Nama</label>
                <input type="text" class="form-control form-control-sm" id="search" name="search"
                       value="{{ $search }}" placeholder="Nama karyawan...">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm">
                    <i class="bi bi-search"></i> Cari
                </button>
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
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $att)
                        <tr>
                            <td>{{ $attendances->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2"
                                         style="width: 28px; height: 28px; background: var(--gv-green-glow); color: var(--gv-green); font-weight: 700; font-size: 0.7rem;">
                                        {{ strtoupper(substr($att->user->name, 0, 1)) }}
                                    </div>
                                    {{ $att->user->name }}
                                </div>
                            </td>
                            <td>{{ $att->date->format('d/m/Y') }}</td>
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
                            <td colspan="6" class="text-center py-4" style="color: var(--gv-text-dim);">Tidak ada data absensi.</td>
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
