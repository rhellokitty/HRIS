<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->input('date', now()->toDateString());
        $status = $request->input('status');
        $search = $request->input('search');

        $query = Attendance::with(['user', 'schedule.shift'])
            ->whereDate('clock_in_time', $date)
            ->latest('clock_in_time');

        if ($status && in_array($status, Attendance::STATUSES, true)) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->search($search);
            });
        }

        $attendances = $query->paginate(20)->withQueryString();

        // Rekap ringkas hari terpilih
        $daySummary = [
            'total' => Attendance::whereDate('clock_in_time', $date)->count(),
            'hadir' => Attendance::whereDate('clock_in_time', $date)->where('status', Attendance::STATUS_HADIR)->count(),
            'telat' => Attendance::whereDate('clock_in_time', $date)->where('status', Attendance::STATUS_TELAT)->count(),
            'alpha' => Attendance::whereDate('clock_in_time', $date)->where('status', Attendance::STATUS_ALPHA)->count(),
        ];

        return view('admin.attendances.index', [
            'attendances' => $attendances,
            'selectedDate' => $date,
            'selectedStatus' => $status,
            'search' => $search,
            'daySummary' => $daySummary,
        ]);
    }

    public function show(Attendance $attendance): View
    {
        $attendance->load(['user', 'schedule.shift']);

        return view('admin.attendances.show', compact('attendance'));
    }
}
