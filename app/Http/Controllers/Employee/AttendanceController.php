<?php

namespace App\Http\Controllers\Employee;

use App\Exceptions\AttendanceException;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $this->attendanceService->status($user);

        $recentAttendances = Attendance::with('schedule.shift')
            ->where('user_id', $user->id)
            ->latest('clock_in_time')
            ->limit(5)
            ->get();

        return view('employee.attendance.index', [
            'user' => $user,
            'schedule' => $status['schedule'],
            'openAttendance' => $status['open'],
            'recordedAttendance' => $status['recorded'],
            'action' => $status['action'],
            'statusMessage' => $status['message'],
            'onLeave' => $status['onLeave'],
            'recentAttendances' => $recentAttendances,
            'office' => config('hris.office'),
        ]);
    }

    public function clockIn(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'latitude.required' => 'Titik lokasi (Latitude) belum terdeteksi. Izinkan akses GPS browser.',
            'longitude.required' => 'Titik lokasi (Longitude) belum terdeteksi.',
        ]);

        try {
            $attendance = $this->attendanceService->clockIn(
                $request->user(),
                $validated['latitude'],
                $validated['longitude']
            );

            $message = 'Clock-in berhasil pukul '.$attendance->clock_in_time->format('H:i').' WIB. Status: '.$attendance->status_label.'.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'attendance' => $attendance,
                ]);
            }

            return redirect()->route('employee.attendance.index')->with('success', $message);
        } catch (AttendanceException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function clockOut(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ], [
            'latitude.required' => 'Titik lokasi (Latitude) belum terdeteksi. Izinkan akses GPS browser.',
            'longitude.required' => 'Titik lokasi (Longitude) belum terdeteksi.',
        ]);

        try {
            $attendance = $this->attendanceService->clockOut(
                $request->user(),
                $validated['latitude'],
                $validated['longitude']
            );

            $message = 'Clock-out berhasil pukul '.$attendance->clock_out_time->format('H:i').' WIB. Durasi kerja: '.$attendance->work_duration_label.'.';

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'attendance' => $attendance,
                ]);
            }

            return redirect()->route('employee.attendance.index')->with('success', $message);
        } catch (AttendanceException $e) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }

            return back()->with('error', $e->getMessage());
        }
    }

    public function history(Request $request): View
    {
        $user = $request->user();

        $attendances = Attendance::with('schedule.shift')
            ->where('user_id', $user->id)
            ->latest('clock_in_time')
            ->paginate(15);

        return view('employee.attendance.history', compact('attendances'));
    }
}
