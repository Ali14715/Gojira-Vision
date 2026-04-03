@extends('layouts.app')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $index => $att)
                        <tr>
                            <td><span class="neo-tag" style="font-size: 0.75rem;">{{ $attendances->firstItem() + $index }}</span></td>
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
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4" style="color: var(--gv-text-dim);">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Belum ada riwayat absensi.</p>
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
