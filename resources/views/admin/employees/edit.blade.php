@extends('layouts.app')

@section('title', 'Edit Karyawan')

@section('content')
<div class="row g-4">
    {{-- Left: Employee Info Card --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body text-center p-4">
                <div class="rounded-circle d-flex align-items-center justify-content-center mx-auto sidebar-avatar" style="width: 80px; height: 80px; font-size: 2rem;">
                    {{ strtoupper(substr($employee->name, 0, 1)) }}
                </div>
                <h5 class="mt-3 mb-1">{{ $employee->name }}</h5>
                <div class="mb-2"><span class="neo-tag" style="font-size: 0.8rem;">{{ $employee->employee_id ?? '-' }}</span></div>
                <span class="neo-tag green mb-3">{{ $employee->position ?? 'Belum diatur' }}</span>

                <div class="neo-divider mt-3">Info</div>

                <div class="d-flex flex-column gap-2 text-start">
                    <div class="mini-stat">
                        <div class="mini-stat-icon" style="background: rgba(59,130,246,0.12); color: var(--gv-info);">
                            <i class="bi bi-envelope"></i>
                        </div>
                        <div>
                            <div class="mini-stat-label">Email</div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--gv-text);">{{ $employee->email }}</div>
                        </div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon" style="background: var(--gv-green-glow); color: var(--gv-green);">
                            <i class="bi bi-building"></i>
                        </div>
                        <div>
                            <div class="mini-stat-label">Departemen</div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--gv-text);">{{ $employee->department?->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon" style="background: {{ $employee->face_registered ? 'var(--gv-green-glow)' : 'rgba(239,68,68,0.1)' }}; color: {{ $employee->face_registered ? 'var(--gv-green)' : 'var(--gv-danger)' }};">
                            <i class="bi bi-person-bounding-box"></i>
                        </div>
                        <div>
                            <div class="mini-stat-label">Wajah</div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: {{ $employee->face_registered ? 'var(--gv-green)' : 'var(--gv-danger)' }};">
                                {{ $employee->face_registered ? 'Terdaftar (' . $employee->faceDescriptors()->count() . ' data)' : 'Belum Terdaftar' }}
                            </div>
                        </div>
                    </div>
                    <div class="mini-stat">
                        <div class="mini-stat-icon" style="background: rgba(139,92,246,0.12); color: #8b5cf6;">
                            <i class="bi bi-calendar3"></i>
                        </div>
                        <div>
                            <div class="mini-stat-label">Terdaftar Sejak</div>
                            <div style="font-size: 0.85rem; font-weight: 600; color: var(--gv-text);">{{ $employee->created_at->format('d M Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Edit Form --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-pencil-square me-2" style="color: var(--gv-green);"></i>Edit Data Karyawan</h6>
            </div>
            <div class="card-body p-4">
                <form method="POST" action="{{ route('admin.employees.update', $employee) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Nama Lengkap</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $employee->name) }}" required>
                            </div>
                            @error('name')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control @error('email') is-invalid @enderror"
                                       id="email" name="email" value="{{ old('email', $employee->email) }}" required>
                            </div>
                            @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="position" class="form-label">Jabatan</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-briefcase"></i></span>
                                <input type="text" class="form-control @error('position') is-invalid @enderror"
                                       id="position" name="position" value="{{ old('position', $employee->position) }}">
                            </div>
                            @error('position')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="department_id" class="form-label">Departemen</label>
                            <select class="form-select @error('department_id') is-invalid @enderror"
                                    id="department_id" name="department_id">
                                <option value="">-- Pilih Departemen --</option>
                                @foreach($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ old('department_id', $employee->department_id) == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('department_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">Telepon</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                                <input type="text" class="form-control @error('phone') is-invalid @enderror"
                                       id="phone" name="phone" value="{{ old('phone', $employee->phone) }}">
                            </div>
                            @error('phone')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="neo-divider mt-3">Ubah Password</div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Password Baru <span style="font-size: 0.7rem; color: var(--gv-text-dim);">(kosongkan jika tidak diubah)</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control @error('password') is-invalid @enderror"
                                       id="password" name="password" placeholder="Minimal 6 karakter">
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                                <input type="password" class="form-control" id="password_confirmation"
                                       name="password_confirmation" placeholder="Ulangi password">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Kembali
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Update Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
