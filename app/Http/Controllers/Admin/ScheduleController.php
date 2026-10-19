<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Schedule;
use App\Models\Shift;
use App\Models\User;
use App\Services\ShiftService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ScheduleController extends Controller
{
    public function __construct(
        protected ShiftService $shiftService
    ) {}

    public function index(Request $request): View
    {
        $selectedDate = $request->input('date') ? Carbon::parse($request->input('date')) : now();
        $viewMode = $request->input('view', 'day'); // 'day' or 'week'

        if ($viewMode === 'week') {
            $startDate = $selectedDate->copy()->startOfWeek();
            $endDate = $startDate->copy()->endOfWeek();

            $schedules = Schedule::with(['user', 'shift'])
                ->betweenDates($startDate, $endDate)
                ->orderBy('date')
                ->get()
                ->groupBy(fn ($item) => $item->date->toDateString());
        } else {
            $startDate = $selectedDate;
            $endDate = $selectedDate;

            $schedules = Schedule::with(['user', 'shift', 'attendance'])
                ->forDate($selectedDate)
                ->join('shifts', 'schedules.shift_id', '=', 'shifts.id')
                ->orderBy('shifts.start_time')
                ->select('schedules.*')
                ->paginate(20)
                ->withQueryString();
        }

        $shifts = Shift::orderBy('name')->get();
        $employees = User::karyawan()->orderBy('name')->get();

        return view('admin.schedules.index', [
            'schedules' => $schedules,
            'selectedDate' => $selectedDate,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'viewMode' => $viewMode,
            'shifts' => $shifts,
            'employees' => $employees,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'date' => ['required', 'date'],
        ]);

        $exists = Schedule::where('user_id', $validated['user_id'])
            ->whereDate('date', $validated['date'])
            ->exists();

        if ($exists) {
            return back()->with('error', 'Karyawan ini sudah memiliki jadwal pada tanggal yang dipilih.');
        }

        Schedule::create($validated);

        return back()->with('success', 'Jadwal shift berhasil ditambahkan.');
    }

    public function generateWeekly(Request $request): RedirectResponse
    {
        $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : now()->addWeek()->startOfWeek();

        try {
            $result = $this->shiftService->generateWeekly($startDate);

            $message = sprintf(
                'Auto-Generate Jadwal selesai: %d jadwal baru dibuat untuk periode %s s/d %s (%d jadwal sudah ada dilewati).',
                $result['created'],
                Carbon::parse($result['start_date'])->translatedFormat('d M Y'),
                Carbon::parse($result['end_date'])->translatedFormat('d M Y'),
                $result['skipped']
            );

            return redirect()->route('admin.schedules.index', [
                'date' => $result['start_date'],
                'view' => 'week',
            ])->with('success', $message);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal generate jadwal: '.$e->getMessage());
        }
    }

    public function destroy(Schedule $schedule): RedirectResponse
    {
        if ($schedule->attendance()->exists()) {
            return back()->with('error', 'Jadwal tidak dapat dihapus karena sudah memiliki rekaman absensi.');
        }

        $schedule->delete();

        return back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
