<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $currentDate = $request->input('date', now()->toDateString());

        $attendances = Attendance::with(['user', 'attendanceBreaks'])
            ->whereDate('work_date', $currentDate)
            ->orderBy('clock_in_at')
            ->get();

        return view('admin.attendance.index', compact('attendances', 'currentDate'));
    }

    public function show(Attendance $attendance)
    {
        $attendance->load(['user','attendanceBreaks']);

        return view('admin.attendance.show', compact('attendance'));
    }

    public function staffAttendanceList(Request $request, User $user)
    {
        $currentMonth = $request->input('month', now()->format('Y-m'));

        $startOfMonth = \Carbon\Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($currentMonth)->endOfMonth();

        $attendances = Attendance::with('attendanceBreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function ($attendance) {
                return \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $dates = [];

        $date = $startOfMonth->copy();

        while ($date <= $endOfMonth) {
            $dates[] = $date->copy();
            $date->addDay();
        }

        return view('admin.attendance.staff', compact(
            'user',
            'attendances',
            'currentMonth',
            'dates'
        ));
    }

    public function exportStaffCsv(Request $request, User $user)
    {
        $currentMonth = $request->input('month', now()->format('Y-m'));

        $startOfMonth = \Carbon\Carbon::parse($currentMonth)->startOfMonth();
        $endOfMonth = \Carbon\Carbon::parse($currentMonth)->endOfMonth();

        $attendances = Attendance::with('attendanceBreaks')
            ->where('user_id', $user->id)
            ->whereBetween('work_date', [$startOfMonth, $endOfMonth])
            ->get()
            ->keyBy(function ($attendance) {
                return \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');
            });

        $filename = $user->name . '_' . $currentMonth . '_attendance.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($startOfMonth, $endOfMonth, $attendances) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['日付', '出勤', '退勤', '休憩', '合計']);

            $date = $startOfMonth->copy();

            while ($date <= $endOfMonth) {
                $dateKey = $date->format('Y-m-d');
                $attendance = $attendances->get($dateKey);

                $breakMinutes = 0;
                $workMinutes = 0;

                if ($attendance) {
                    foreach ($attendance->attendanceBreaks as $break) {
                        if ($break->break_start_at && $break->break_end_at) {
                            $breakMinutes += \Carbon\Carbon::parse($break->break_start_at)
                                ->diffInMinutes(\Carbon\Carbon::parse($break->break_end_at));
                        }
                    }

                    if ($attendance->clock_in_at && $attendance->clock_out_at) {
                        $workMinutes =
                            \Carbon\Carbon::parse($attendance->clock_in_at)
                                ->diffInMinutes(\Carbon\Carbon::parse($attendance->clock_out_at))
                            - $breakMinutes;
                    }
                }

                fputcsv($file, [
                    $date->format('m/d') . '(' . ['日', '月', '火', '水', '木', '金', '土'][$date->dayOfWeek] . ')',
                    $attendance && $attendance->clock_in_at ? \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i') : '',
                    $attendance && $attendance->clock_out_at ? \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i') : '',
                    $attendance ? floor($breakMinutes / 60) . ':' . str_pad($breakMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                    $attendance ? floor($workMinutes / 60) . ':' . str_pad($workMinutes % 60, 2, '0', STR_PAD_LEFT) : '',
                ]);

                $date->addDay();
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function update(Request $request, Attendance $attendance)
    {
        $request->validate([
            'clock_in_at' => ['required'],
            'clock_out_at' => ['required'],
            'note' => ['required'],
        ]);

        $workDate = \Carbon\Carbon::parse($attendance->work_date)->format('Y-m-d');

        $attendance->update([
            'clock_in_at' => $workDate . ' ' . $request->clock_in_at . ':00',
            'clock_out_at' => $workDate . ' ' . $request->clock_out_at . ':00',
            'note' => $request->note,
        ]);

        foreach ($attendance->attendanceBreaks as $index => $break) {
            $breakStart = $request->break_start_at[$index] ?? null;
            $breakEnd = $request->break_end_at[$index] ?? null;

            $break->update([
                'break_start_at' => $breakStart ? $workDate . ' ' . $breakStart . ':00' : null,
                'break_end_at' => $breakEnd ? $workDate . ' ' . $breakEnd . ':00' : null,
            ]);
        }

        return redirect()
            ->route('admin.attendance.show', $attendance->id)
            ->with('flashSuccess', '勤怠を修正しました');
    }
}
