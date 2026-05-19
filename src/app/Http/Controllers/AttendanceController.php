<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        $status = '勤務外';

        if ($attendance) {
            $onBreak = AttendanceBreak::where('attendance_id', $attendance->id)
                ->whereNull('break_end_at')
                ->exists();

            if (!is_null($attendance->clock_out_at)) {
                $status = '退勤済';
            } elseif ($onBreak) {
                $status = '休憩中';
            } elseif (!is_null($attendance->clock_in_at)) {
                $status ='出勤中';
            }
        }

        $now = Carbon::now();

        return view('attendance.index', compact('attendance', 'status', 'now'));
    }

    public function clockIn()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        if ($attendance) {
            return redirect()->route('attendance.index')
                ->with('flashError', 'お疲れ様でした。');
        }

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => $today->toDateString(),
            'clock_in_at' => Carbon::now(),
            'status' => '出勤中',
        ]);

        return redirect()->route('attendance.index');
    }

    public function clockOut()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

            if (!$attendance || $attendance->clock_out_at) {
                return redirect()->route('attendance.index')
                    ->with('flashError', '退勤できません。');
            }

            $attendance->update([
                'clock_out_at' => Carbon::now(),
                'status' => '退勤済',
            ]);

            return redirect()->route('attendance.index')
                ->with('flashSuccess', '退勤しました。');
    }

    public function breakStart()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        if (!$attendance) {
            return redirect()->route('attendance.index')
                ->with('flashError', '出勤していません。');
        }

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => Carbon::now(),
        ]);

        return redirect()->route('attendance.index')
            ->with('flashSuccess', '休憩に入りました。');
    }

    public function breakEnd()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('work_date', $today->toDateString())
            ->first();

        $break = AttendanceBreak::where('attendance_id', $attendance->id)
            ->whereNull('break_end_at')
            ->latest()
            ->first();

        if (!$break) {
            return redirect()->route('attendance.index')
                ->with('flashError', '休憩中ではありません。');
        }

        $break->update([
            'break_end_at' => Carbon::now(),
        ]);

        return redirect()->route('attendance.index')
            ->with('flashSuccess', '休憩を終了しました。');
    }

    public function list(Request $request)
    {
        $user = Auth::user();

        $currentMonth = $request->input('month', Carbon::today()->format('Y-m'));
        $currentDate = Carbon::createFromFormat('Y-m', $currentMonth)->startOfMonth();

        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $attendances = Attendance::with('attendanceBreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [
                $startOfMonth->toDateString(),
                $endOfMonth->toDateString()
            ])
            ->orderBy('work_date', 'asc')
            ->get()
            ->keyBy(function ($attendance) {
                return Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $dates = [];

        $date = $startOfMonth->copy();

        while ($date <= $endOfMonth) {
            $dates[] = $date->copy();
            $date->addDay();
        }

        $prevMonth = $currentDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentDate->copy()->addMonth()->format('Y-m');

        return view('attendance.list', compact(
            'attendances',
            'currentMonth',
            'prevMonth',
            'nextMonth',
            'dates'
        ));
    }

    public function show(Attendance $attendance)
    {

        $user = Auth::user();

        if ($attendance->user_id !== $user->id) {
            abort(403);
        }

        $attendance->load('attendanceBreaks', 'attendanceCorrectionRequests');

        $pendingRequestExists = $attendance->attendanceCorrectionRequests()
            ->where('status', 'pending')
            ->exists();

        return view('attendance.show', compact(
            'attendance',
            'pendingRequestExists'
        ));
    }
}
