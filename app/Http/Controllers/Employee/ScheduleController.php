<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        // 2 pekan: pekan ini dan pekan depan
        $start = now()->startOfWeek();
        $end = now()->addWeek()->endOfWeek();

        $schedules = Schedule::with('shift', 'attendance')
            ->where('user_id', $user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->get();

        return view('employee.schedule.index', compact('schedules', 'start', 'end'));
    }
}
