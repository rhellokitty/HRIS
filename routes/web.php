<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\LeaveController as AdminLeaveController;
use App\Http\Controllers\Admin\ScheduleController as AdminScheduleController;
use App\Http\Controllers\Admin\ShiftController as AdminShiftController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\HomeController as EmployeeHomeController;
use App\Http\Controllers\Employee\LeaveController as EmployeeLeaveController;
use App\Http\Controllers\Employee\ProfileController as EmployeeProfileController;
use App\Http\Controllers\Employee\ScheduleController as EmployeeScheduleController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes — HRIS Shift & Absensi
|--------------------------------------------------------------------------
*/

// Root Redirect: otomatis mengarahkan ke dashboard masing-masing role
Route::get('/', function () {
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return redirect()->route($user->homeRoute());
    }

    return redirect()->route('login');
});

// Autentikasi Pengguna
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| Employee Routes (Karyawan — Mobile-First)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:karyawan'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/', [EmployeeHomeController::class, 'index'])->name('home');

    // Modul Absensi (Anti-Manipulasi, Geolocation & Server-Side Time Locking)
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [EmployeeAttendanceController::class, 'index'])->name('index');
        Route::post('/clock-in', [EmployeeAttendanceController::class, 'clockIn'])->name('clock-in');
        Route::post('/clock-out', [EmployeeAttendanceController::class, 'clockOut'])->name('clock-out');
        Route::get('/history', [EmployeeAttendanceController::class, 'history'])->name('history');
    });

    // Pengajuan Cuti / Izin
    Route::prefix('leave')->name('leave.')->group(function () {
        Route::get('/', [EmployeeLeaveController::class, 'index'])->name('index');
        Route::get('/create', [EmployeeLeaveController::class, 'create'])->name('create');
        Route::post('/', [EmployeeLeaveController::class, 'store'])->name('store');
    });

    // Jadwal Shift Karyawan
    Route::get('/schedule', [EmployeeScheduleController::class, 'index'])->name('schedule.index');

    // Profil & Akun
    Route::get('/profile', [EmployeeProfileController::class, 'index'])->name('profile');
    Route::put('/profile/password', [EmployeeProfileController::class, 'updatePassword'])->name('profile.password');
});

/*
|--------------------------------------------------------------------------
| Admin & Operator Routes (Desktop Dashboard)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin,operator'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard Monitoring
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Monitoring Absensi & Live Rekap
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::get('/attendances/{attendance}', [AdminAttendanceController::class, 'show'])->name('attendances.show');

    // Monitoring Jadwal Shift
    Route::get('/schedules', [AdminScheduleController::class, 'index'])->name('schedules.index');

    // Fitur Khusus Admin (Full Access)
    Route::middleware('role:admin')->group(function () {
        // Auto-Generate Jadwal & Penambahan Jadwal
        Route::post('/schedules', [AdminScheduleController::class, 'store'])->name('schedules.store');
        Route::post('/schedules/generate', [AdminScheduleController::class, 'generateWeekly'])->name('schedules.generate');
        Route::delete('/schedules/{schedule}', [AdminScheduleController::class, 'destroy'])->name('schedules.destroy');

        // Master Shift CRUD
        Route::resource('shifts', AdminShiftController::class)->except(['show']);

        // Master User CRUD
        Route::resource('users', AdminUserController::class)->except(['show']);

        // Approval Cuti & Izin
        Route::prefix('leaves')->name('leaves.')->group(function () {
            Route::get('/', [AdminLeaveController::class, 'index'])->name('index');
            Route::post('/{leave}/approve', [AdminLeaveController::class, 'approve'])->name('approve');
            Route::post('/{leave}/reject', [AdminLeaveController::class, 'reject'])->name('reject');
        });
    });
});
