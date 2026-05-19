<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;

class AttendanceCorrectionRequestsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendance = Attendance::whereDate('work_date', '2026-05-19')
            ->where('user_id', 2)
            ->first();

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'requested_clock_in_at' => '2026-05-19 09:30:00',
            'requested_clock_out_at' => '2026-05-19 18:30:00',
            'reason' => '電車遅延のため',
            'status' => 'pending',
        ]);

        $attendance = Attendance::whereDate('work_date', '2026-05-20')
            ->where('user_id', 3)
            ->first();

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $attendance->user_id,
            'requested_clock_in_at' => '2026-05-20 10:00:00',
            'requested_clock_out_at' => '2026-05-20 19:00:00',
            'reason' => '外勤対応のため',
            'status' => 'approved',
        ]);
    }
}
