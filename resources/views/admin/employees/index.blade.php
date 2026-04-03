@extends('layouts.app')

@section('title', 'Data Karyawan')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h5 class="mb-0">Daftar Karyawan</h5>
    <a href="{{ route('admin.employees.create') }}" class="btn btn-dark btn-sm">
        <i class="bi bi-plus-lg"></i> Tambah Karyawan
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
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
                            <td>{{ $employees->firstItem() + $index }}</td>
                            <td>{{ $emp->name }}</td>
                            <td>{{ $emp->email }}</td>
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
                                <a href="{{ route('admin.employees.edit', $emp) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('admin.employees.destroy', $emp) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Yakin hapus karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">Belum ada data karyawan.</td>
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
@endsection
