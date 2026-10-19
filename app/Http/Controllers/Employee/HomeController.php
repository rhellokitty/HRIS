<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $this->attendanceService->status($user);

        // KPI bulan berjalan
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();

        $presentThisMonth = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$startOfMonth, $endOfMonth])
            ->whereIn('status', [Attendance::STATUS_HADIR, Attendance::STATUS_TELAT])
            ->count();

        $lateThisMonth = Attendance::where('user_id', $user->id)
            ->whereBetween('clock_in_time', [$startOfMonth, $endOfMonth])
            ->where('status', Attendance::STATUS_TELAT)
            ->count();

        $approvedLeavesThisYear = LeaveRequest::approved()
            ->where('user_id', $user->id)
            ->whereYear('start_date', now()->year)
            ->where('type', LeaveRequest::TYPE_CUTI)
            ->get()
            ->sum(fn ($req) => $req->duration_days);

        $annualLeaveQuota = 12;
        $remainingLeave = max(0, $annualLeaveQuota - $approvedLeavesThisYear);

        return view('employee.home', [
            'user' => $user,
            'schedule' => $status['schedule'],
            'openAttendance' => $status['open'],
            'recordedAttendance' => $status['recorded'],
            'action' => $status['action'],
            'statusMessage' => $status['message'],
            'presentThisMonth' => $presentThisMonth,
            'lateThisMonth' => $lateThisMonth,
            'remainingLeave' => $remainingLeave,
        ]);
    }
}
