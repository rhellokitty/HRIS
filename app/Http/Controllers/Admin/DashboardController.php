<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->toDateString();

        // 1. Ringkasan KPI (PRD §3.D)
        $totalEmployees = User::karyawan()->count();

        // Hadir hari ini (hadir + telat)
        $todayAttendances = Attendance::clockedInOn($today)->get();
        $presentTodayCount = $todayAttendances->whereIn('status', [Attendance::STATUS_HADIR, Attendance::STATUS_TELAT])->count();
        $lateTodayCount = $todayAttendances->where('status', Attendance::STATUS_TELAT)->count();

        // Cuti / Izin aktif hari ini
        $onLeaveTodayCount = LeaveRequest::approved()->covering($today)->count();

        // Pending approvals
        $pendingLeaveCount = LeaveRequest::pending()->count();

        // Persentase kehadiran terhadap total karyawan
        $presentRate = $totalEmployees > 0 ? round(($presentTodayCount / $totalEmployees) * 100, 1) : 0;

        // 2. Tabel Live Shift Operasional Hari Ini (PRD §3.D)
        $todaySchedules = Schedule::with(['user', 'shift', 'attendance'])
            ->whereDate('date', $today)
            ->join('shifts', 'schedules.shift_id', '=', 'shifts.id')
            ->orderBy('shifts.start_time')
            ->select('schedules.*')
            ->get();

        // Data master shifts aktif
        $activeShifts = Shift::withCount(['schedules' => function ($q) use ($today) {
            $q->whereDate('date', $today);
        }])->get();

        return view('admin.dashboard', [
            'totalEmployees' => $totalEmployees,
            'presentTodayCount' => $presentTodayCount,
            'lateTodayCount' => $lateTodayCount,
            'onLeaveTodayCount' => $onLeaveTodayCount,
            'pendingLeaveCount' => $pendingLeaveCount,
            'presentRate' => $presentRate,
            'todaySchedules' => $todaySchedules,
            'activeShifts' => $activeShifts,
            'today' => now(),
        ]);
    }
}
