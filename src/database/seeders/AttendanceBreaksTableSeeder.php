<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\AttendanceBreak;

class AttendanceBreaksTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $attendances = Attendance::all();

        foreach ($attendances as $attendance) {

            AttendanceBreak::create([
                'attendance_id' => $attendance->id,
                'break_start_at' => $attendance->work_date . ' 12:00:00',
                'break_end_at' => $attendance->work_date . ' 13:00:00',
            ]);
        }
    }
}
