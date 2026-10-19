<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->input('status', LeaveRequest::STATUS_PENDING);

        $query = LeaveRequest::with('user')->latest('created_at');

        if ($status && in_array($status, array_keys(LeaveRequest::STATUSES), true)) {
            $query->where('status', $status);
        }

        $leaves = $query->paginate(15)->withQueryString();

        $counts = [
            'pending' => LeaveRequest::pending()->count(),
            'approved' => LeaveRequest::approved()->count(),
            'rejected' => LeaveRequest::where('status', LeaveRequest::STATUS_REJECTED)->count(),
        ];

        return view('admin.leaves.index', compact('leaves', 'status', 'counts'));
    }

    public function approve(LeaveRequest $leave): RedirectResponse
    {
        $leave->update(['status' => LeaveRequest::STATUS_APPROVED]);

        return back()->with('success', 'Pengajuan cuti/izin untuk '.$leave->user->name.' telah disetujui.');
    }

    public function reject(LeaveRequest $leave): RedirectResponse
    {
        $leave->update(['status' => LeaveRequest::STATUS_REJECTED]);

        return back()->with('warning', 'Pengajuan cuti/izin untuk '.$leave->user->name.' telah ditolak.');
    }
}
