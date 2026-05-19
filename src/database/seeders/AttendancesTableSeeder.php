<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AttendancesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = User::where('role', 'general')->get();

        foreach ($users as $user) {

            $startDate = Carbon::create(2026, 5, 1);
            $endDate = Carbon::create(2026, 5, 31);

            while ($startDate <= $endDate) {

                if (!$startDate->isWeekend()) {

                    Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $startDate->format('Y-m-d'),
                        'clock_in_at' => $startDate->format('Y-m-d') . ' 09:00:00',
                        'clock_out_at' => $startDate->format('Y-m-d') . ' 18:00:00',
                        'note' => null,
                    ]);
                }

                $startDate->addDay();
            }
        }

        Attendance::where('user_id', 2)
            ->where('work_date', '2026-05-10')
            ->update([
                'clock_out_at' => '2026-05-10 20:00:00',
                'note' => '展示会対応',
            ]);

        Attendance::where('user_id', 3)
            ->where('work_date', '2026-05-15')
            ->update([
                'clock_in_at' => '2026-05-15 10:00:00',
                'clock_out_at' => '2026-05-15 19:00:00',
                'note' => '外勤対応',
            ]);

        Attendance::where('user_id', 4)
            ->where('work_date', '2026-05-20')
            ->update([
                'clock_out_at' => '2026-05-20 21:00:00',
                'note' => '月末棚卸し',
            ]);

        Attendance::where('user_id', 5)
            ->where('work_date', '2026-05-08')
            ->update([
                'clock_in_at' => '2026-05-08 08:00:00',
                'clock_out_at' => '2026-05-08 17:00:00',
                'note' => '朝礼準備',
            ]);

        Attendance::where('user_id', 6)
            ->where('work_date', '2026-05-22')
            ->update([
                'clock_out_at' => '2026-05-22 19:30:00',
                'note' => '会議対応',
            ]);

        Attendance::where('user_id', 7)
            ->where('work_date', '2026-05-27')
            ->update([
                'clock_in_at' => '2026-05-27 11:00:00',
                'clock_out_at' => '2026-05-27 20:00:00',
                'note' => '外部研修参加',
            ]);
    }
}
