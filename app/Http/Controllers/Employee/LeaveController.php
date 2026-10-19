<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $leaves = LeaveRequest::where('user_id', $request->user()->id)
            ->latest('created_at')
            ->paginate(10);

        return view('employee.leave.index', compact('leaves'));
    }

    public function create(): View
    {
        return view('employee.leave.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'type' => ['required', Rule::in(array_keys(LeaveRequest::TYPES))],
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai cuti tidak boleh di masa lalu.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'reason.required' => 'Keterangan atau alasan pengajuan wajib diisi.',
            'reason.min' => 'Keterangan minimal 5 karakter.',
        ]);

        // Cek overlap dengan pengajuan yang sudah ada
        $overlap = LeaveRequest::where('user_id', $request->user()->id)
            ->whereIn('status', [LeaveRequest::STATUS_PENDING, LeaveRequest::STATUS_APPROVED])
            ->overlapping($validated['start_date'], $validated['end_date'])
            ->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'Anda sudah memiliki pengajuan cuti/izin aktif di rentang tanggal tersebut.');
        }

        LeaveRequest::create([
            'user_id' => $request->user()->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'status' => LeaveRequest::STATUS_PENDING,
        ]);

        return redirect()->route('employee.leave.index')->with('success', 'Pengajuan berhasil dikirim dan menunggu persetujuan HRD.');
    }
}
