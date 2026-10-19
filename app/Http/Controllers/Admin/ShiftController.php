<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::withCount('schedules')
            ->orderBy('start_time')
            ->get();

        return view('admin.shifts.index', compact('shifts'));
    }

    public function create(): View
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', 'unique:shifts,name'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ], [
            'name.unique' => 'Nama shift sudah digunakan.',
            'start_time.date_format' => 'Format jam mulai harus JJ:MM (contoh: 08:00).',
            'end_time.date_format' => 'Format jam selesai harus JJ:MM (contoh: 16:00).',
        ]);

        Shift::create($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Master Shift "'.$validated['name'].'" berhasil ditambahkan.');
    }

    public function edit(Shift $shift): View
    {
        return view('admin.shifts.edit', compact('shift'));
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:50', Rule::unique('shifts', 'name')->ignore($shift->id)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ], [
            'name.unique' => 'Nama shift sudah digunakan.',
            'start_time.date_format' => 'Format jam mulai harus JJ:MM (contoh: 08:00).',
            'end_time.date_format' => 'Format jam selesai harus JJ:MM (contoh: 16:00).',
        ]);

        $shift->update($validated);

        return redirect()->route('admin.shifts.index')->with('success', 'Master Shift "'.$shift->name.'" berhasil diperbarui.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        if ($shift->schedules()->exists()) {
            return back()->with('error', 'Shift tidak dapat dihapus karena masih digunakan pada jadwal kerja.');
        }

        $shiftName = $shift->name;
        $shift->delete();

        return redirect()->route('admin.shifts.index')->with('success', 'Master Shift "'.$shiftName.'" berhasil dihapus.');
    }
}
