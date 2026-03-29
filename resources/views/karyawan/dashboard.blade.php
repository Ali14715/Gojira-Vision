@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-md-4">
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
    <div class="col-md-4">
        <div class="stat-card warning">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <div class="stat-number">{{ $totalTerlambat }}</div>
                    <div class="stat-label">Terlambat Bulan Ini</div>
                </div>
                <i class="bi bi-clock-fill stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card {{ $todayAttendance ? '' : 'danger' }}">
            <div class="text-center">
                @if($todayAttendance)
                    <i class="bi bi-check-circle-fill mb-2" style="font-size: 2rem; color: var(--gv-green);"></i>
                    <div class="fw-bold" style="color: var(--gv-green);">Sudah Absen</div>
                    <div class="stat-label">Masuk: {{ $todayAttendance->clock_in }}</div>
                    @if($todayAttendance->clock_out)
                        <div class="stat-label">Pulang: {{ $todayAttendance->clock_out }}</div>
                    @endif
                @else
                    <i class="bi bi-x-circle-fill mb-2" style="font-size: 2rem; color: var(--gv-danger);"></i>
                    <div class="fw-bold" style="color: var(--gv-danger);">Belum Absen</div>
                    <a href="{{ route('karyawan.attendance') }}" class="btn btn-primary btn-sm mt-2">
                        <i class="bi bi-camera-video-fill me-1"></i> Absen Sekarang
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>

@if(!auth()->user()->face_registered)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-1"></i>
        <strong>Perhatian!</strong> Anda belum mendaftarkan wajah.
        <a href="{{ route('karyawan.face.register') }}" class="alert-link">Daftar wajah sekarang</a> untuk bisa menggunakan absensi.
    </div>
@endif
@endsection
