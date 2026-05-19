<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use App\Http\Requests\AttendanceCorrectionRequest as AttendanceCorrectionFormRequest;

class StampCorrectionRequestController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $status = request('status', 'pending');

        if ($user->role === 'admin') {
            $correctionRequests = AttendanceCorrectionRequest::with(['attendance.user'])
                ->where('status', $status)
                ->orderBy('created_at','desc')
                ->get();
        } else {
            $correctionRequests = AttendanceCorrectionRequest::with(['attendance'])
                ->where('user_id', $user->id)
                ->where('status', $status)
                ->orderBy('created_at','desc')
                ->get();
        }

        return view('stamp_correction_request.list', compact('correctionRequests', 'status'));
    }

    public function store(AttendanceCorrectionFormRequest $request, Attendance $attendance)
    {
        $user = Auth::user();

        if ($attendance->user_id !== $user->id) {
            abort(403);
        }

        $pendingRequestExists = $attendance->attendanceCorrectionRequests()
            ->where('status', 'pending')
            ->exists();

        if ($pendingRequestExists) {
            return redirect()->route('attendance.show', $attendance->id)
                ->with('flashError', '承認待ちのため修正申請はできません。');
        }

        $correctionRequest = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => $attendance->work_date . ' ' . $request->requested_clock_in_at . ':00',
            'requested_clock_out_at' => $attendance->work_date . ' ' . $request->requested_clock_out_at . ':00',
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        foreach ($request->breaks ?? [] as $break) {
            if (!empty($break['break_start_at']) && !empty($break['break_end_at'])) {
                $correctionRequest->attendanceCorrectionRequestBreaks()->create([
                    'requested_break_start_at' => $attendance->work_date . ' ' . $break['break_start_at'] . ':00',
                    'requested_break_end_at' => $attendance->work_date . ' ' . $break['break_end_at'] . ':00',
                ]);
            }
        }

        return redirect()->route('stamp_correction_request.index')
            ->with('flashSuccess', '修正申請を送信しました。');
    }
}