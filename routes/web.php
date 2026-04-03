<?php

use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AttendanceReportController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\FaceRegistrationController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\KioskController;
use Illuminate\Support\Facades\Route;

// ============ AUTH ============
Route::get('/', fn() => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ============ ADMIN ============
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('employees', EmployeeController::class)->except('show');

    Route::get('/attendances', [AttendanceReportController::class, 'index'])->name('attendances.index');

    Route::resource('departments', DepartmentController::class)->except(['show', 'create', 'edit']);

    Route::get('schedules', [ScheduleController::class, 'index'])->name('schedules.index');
    Route::post('schedules/bulk-update', [ScheduleController::class, 'bulkUpdate'])->name('schedules.bulkUpdate');
});

// ============ KARYAWAN ============
Route::middleware(['auth', 'role:karyawan'])->prefix('karyawan')->name('karyawan.')->group(function () {
    Route::get('/dashboard', [KaryawanController::class, 'dashboard'])->name('dashboard');
    Route::get('/history', [KaryawanController::class, 'history'])->name('history');

    // Face Registration
    Route::get('/face-register', [FaceRegistrationController::class, 'index'])->name('face.register');
    Route::post('/face-register', [FaceRegistrationController::class, 'store'])->name('face.store');

    // Attendance
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance');
    Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clockIn');
    Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clockOut');
});

// ============ API (untuk face-api.js) ============
Route::middleware('auth')->group(function () {
    Route::get('/api/face-descriptors', [FaceRegistrationController::class, 'allDescriptors']);
});

// ============ KIOSK (Publik - tanpa login) ============
Route::get('/kiosk', [KioskController::class, 'index'])->name('kiosk');
Route::post('/kiosk/clock', [KioskController::class, 'clockIn'])->name('kiosk.clock');
Route::get('/kiosk/descriptors', [KioskController::class, 'descriptors'])->name('kiosk.descriptors');
