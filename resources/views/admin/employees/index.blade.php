@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Daftar Karyawan</h5>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Karyawan
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Departemen</th>
                        <th>Jabatan</th>
                        <th>Telepon</th>
                        <th>Wajah</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $index => $emp)
                        <tr>
                            <td><span class="neo-tag" style="font-size: 0.75rem;">{{ $emp->employee_id ?? '-' }}</span></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center me-2 sidebar-avatar" style="width: 30px; height: 30px; font-size: 0.7rem;">
                                        {{ strtoupper(substr($emp->name, 0, 1)) }}
                                    </div>
                                    {{ $emp->name }}
                                </div>
                            </td>
                            <td><span style="font-family: 'JetBrains Mono', monospace; font-size: 0.85rem;">{{ $emp->email }}</span></td>
                            <td>{{ $emp->department?->name ?? '-' }}</td>
                            <td>{{ $emp->position ?? '-' }}</td>
                            <td>{{ $emp->phone ?? '-' }}</td>
                            <td>
                                @if($emp->face_registered)
                                    <span class="badge bg-success">Terdaftar</span>
                                @else
                                    <span class="badge bg-secondary">Belum</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#viewModal{{ $emp->id }}" title="Lihat Detail">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <a href="{{ route('admin.employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('admin.employees.destroy', $emp) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin hapus karyawan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4" style="color: var(--gv-text-dim);">
                                <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Belum ada data karyawan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    {{ $employees->links() }}
</div>

{{-- View Modals --}}
@foreach($employees as $emp)
<div class="modal fade" id="viewModal{{ $emp->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title"><i class="bi bi-person-badge me-2" style="color: var(--gv-green);"></i>Detail Karyawan</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-4">
                    {{-- Left: Info --}}
                    <div class="col-md-6">
                        <div class="text-center mb-4">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto sidebar-avatar" style="width: 72px; height: 72px; font-size: 1.8rem;">
                                {{ strtoupper(substr($emp->name, 0, 1)) }}
                            </div>
                            <h5 class="mt-3 mb-1">{{ $emp->name }}</h5>
                            <div class="mb-2"><span class="neo-tag" style="font-size: 0.8rem;">{{ $emp->employee_id ?? '-' }}</span></div>
                            <span class="neo-tag green">{{ $emp->position ?? 'Belum diatur' }}</span>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: rgba(59,130,246,0.12); color: var(--gv-info);">
                                    <i class="bi bi-envelope"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Email</div>
                                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--gv-text);">{{ $emp->email }}</div>
                                </div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: var(--gv-green-glow); color: var(--gv-green);">
                                    <i class="bi bi-building"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Departemen</div>
                                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--gv-text);">{{ $emp->department?->name ?? 'Belum diatur' }}</div>
                                </div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: rgba(245,158,11,0.12); color: var(--gv-warning);">
                                    <i class="bi bi-telephone"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Telepon</div>
                                    <div style="font-size: 0.9rem; font-weight: 600; color: var(--gv-text);">{{ $emp->phone ?? '-' }}</div>
                                </div>
                            </div>
                            <div class="mini-stat">
                                <div class="mini-stat-icon" style="background: rgba(139,92,246,0.12); color: #8b5cf6;">
                                    <i class="bi bi-calendar-week"></i>
                                </div>
                                <div>
                                    <div class="mini-stat-label">Jadwal</div>
                                    <div style="font-size: 0.85rem; font-weight: 600; color: var(--gv-text);">{{ $emp->schedule_summary }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right: Face Descriptors --}}
                    <div class="col-md-6">
                        <h6 class="mb-3"><i class="bi bi-person-bounding-box me-2" style="color: var(--gv-green);"></i>Citra Wajah Terdaftar</h6>
                        @if($emp->face_registered && $emp->faceDescriptors->count() > 0)
                            <div class="alert alert-success py-2">
                                <i class="bi bi-check-circle me-1"></i>
                                <strong>{{ $emp->faceDescriptors->count() }}</strong> wajah terdaftar
                            </div>
                            <div class="row g-2">
                                @foreach($emp->faceDescriptors as $fd)
                                    <div class="col-6">
                                        <div class="p-3 text-center" style="border: 2px solid var(--gv-border); border-radius: 8px; background: var(--gv-bg-input);">
                                            <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 48px; height: 48px; background: var(--gv-green-glow); border: 2px solid var(--gv-green);">
                                                <i class="bi bi-person-check-fill" style="font-size: 1.3rem; color: var(--gv-green);"></i>
                                            </div>
                                            <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--gv-text-dim);">
                                                Descriptor #{{ $loop->iteration }}
                                            </div>
                                            <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.65rem; color: var(--gv-text-dim);">
                                                {{ $fd->label ?? 'face_' . $fd->id }}
                                            </div>
                                            <div style="font-family: 'JetBrains Mono', monospace; font-size: 0.6rem; color: var(--gv-text-dim);" class="mt-1">
                                                {{ $fd->created_at->format('d/m/Y H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <canvas id="facePreview{{ $emp->id }}" width="200" height="200" style="display: none;"></canvas>
                        @else
                            <div class="text-center py-4" style="color: var(--gv-text-dim);">
                                <i class="bi bi-person-x" style="font-size: 3rem;"></i>
                                <p class="mt-2 mb-0">Belum ada wajah terdaftar.</p>
                                <span style="font-size: 0.8rem;">Karyawan perlu mendaftar wajah melalui akun mereka.</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="{{ route('admin.employees.edit', $emp) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Karyawan
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection
