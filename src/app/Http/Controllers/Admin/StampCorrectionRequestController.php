<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceCorrectionRequest;


class StampCorrectionRequestController extends Controller
{
    public function show(AttendanceCorrectionRequest $attendanceCorrectionRequest)
    {
        $attendanceCorrectionRequest->load([
            'attendance.user',
            'attendance.attendanceBreaks',
            'attendanceCorrectionRequestBreaks',
        ]);

        return view('admin.stamp_correction_request.show', [
            'request' => $attendanceCorrectionRequest,
        ]);
    }

    public function approve(AttendanceCorrectionRequest $attendanceCorrectionRequest)
    {
        $attendance = $attendanceCorrectionRequest->attendance;

        $attendance->update([
            'clock_in_at' => $attendanceCorrectionRequest->requested_clock_in_at,
            'clock_out_at' => $attendanceCorrectionRequest->requested_clock_out_at,
            'note' => $attendanceCorrectionRequest->reason,
        ]);

        $attendance->attendanceBreaks()->delete();

        foreach ($attendanceCorrectionRequest->attendanceCorrectionRequestBreaks as $break) {
            $attendance->attendanceBreaks()->create([
                'break_start_at' => $break->requested_break_start_at,
                'break_end_at' => $break->requested_break_end_at,
            ]);
        }

        $attendanceCorrectionRequest->update([
            'status' => 'approved',
        ]);

        return redirect()->route('stamp_correction_request.index')
            ->with('flashSuccess', '申請を承認しました。');
    }

    public function reject(AttendanceCorrectionRequest $attendanceCorrectionRequest)
    {
        $attendanceCorrectionRequest->update([
            'status' => 'rejected',
        ]);

        return redirect()->route('stamp_correction_request.index')
            ->with('flashSuccess', '申請を却下しました。');
    }
}
