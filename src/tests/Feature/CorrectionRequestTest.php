<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorrectionRequestTest extends TestCase
{
    use RefreshDatabase;

    private function createUser()
    {
        $user = User::create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $user->markEmailAsVerified();

        return $user;
    }

    /** @test */
    public function 勤怠修正申請が保存される()
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => now()->setTime(12, 0),
            'break_end_at' => now()->setTime(13, 0),
        ]);

        $this->actingAs($user)
            ->post(route('stamp_correction_request.store', $attendance->id), [
                'requested_clock_in_at' => '09:30',
                'requested_clock_out_at' => '18:30',
                'breaks' => [
                    [
                        'break_start_at' => '12:00',
                        'break_end_at' => '13:00',
                    ],
                ],
                'reason' => '電車遅延のため',
            ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'reason' => '電車遅延のため',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function 承認待ちの申請が一覧に表示される()
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => now()->setTime(9, 30),
            'requested_clock_out_at' => now()->setTime(18, 30),
            'reason' => '電車遅延のため',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)
            ->get(route('stamp_correction_request.index', ['status' => 'pending']));

        $response->assertSee('承認待ち');
        $response->assertSee('電車遅延のため');
    }
}