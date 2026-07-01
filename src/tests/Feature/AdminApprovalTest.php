<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        $admin = User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $admin->markEmailAsVerified();

        return $admin;
    }

    private function createGeneralUser()
    {
        $user = User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => bcrypt('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $user->markEmailAsVerified();

        return $user;
    }

    /** @test */
    public function 管理者が修正申請を承認できる()
    {
        $admin = $this->createAdmin();

        $user = $this->createGeneralUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $request = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => now()->setTime(10, 0),
            'requested_clock_out_at' => now()->setTime(19, 0),
            'reason' => '電車遅延',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stamp_correction_request.approve', $request->id));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $request->id,
            'status' => 'approved',
        ]);
    }

    /** @test */
    public function 承認後に勤怠情報へ反映される()
    {
        $admin = $this->createAdmin();

        $user = $this->createGeneralUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now()->setTime(9, 0),
            'clock_out_at' => now()->setTime(18, 0),
        ]);

        $request = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => now()->setTime(10, 0),
            'requested_clock_out_at' => now()->setTime(19, 0),
            'reason' => '電車遅延',
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.stamp_correction_request.approve', $request->id));

        $attendance->refresh();

        $this->assertEquals(
            '10:00',
            \Carbon\Carbon::parse($attendance->clock_in_at)->format('H:i')
        );

        $this->assertEquals(
            '19:00',
            \Carbon\Carbon::parse($attendance->clock_out_at)->format('H:i')
        );
    }

    /** @test */
    public function 管理者は承認待ちの修正申請を一覧で確認できる()
    {
        $admin = $this->createAdmin();
        $user = $this->createGeneralUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-10',
            'clock_in_at' => '2026-06-10 09:00:00',
            'clock_out_at' => '2026-06-10 18:00:00',
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => '2026-06-10 10:00:00',
            'requested_clock_out_at' => '2026-06-10 19:00:00',
            'reason' => '電車遅延',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('stamp_correction_request.index', ['status' => 'pending']));

        $response->assertOk();
        $response->assertSee('承認待ち');
        $response->assertSee('一般ユーザー');
        $response->assertSee('電車遅延');
        $response->assertSee('2026/06/10');
    }

    /** @test */
    public function 管理者は承認済みの修正申請を一覧で確認できる()
    {
        $admin = $this->createAdmin();
        $user = $this->createGeneralUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-11',
            'clock_in_at' => '2026-06-11 09:00:00',
            'clock_out_at' => '2026-06-11 18:00:00',
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => '2026-06-11 10:00:00',
            'requested_clock_out_at' => '2026-06-11 19:00:00',
            'reason' => '通院のため',
            'status' => 'approved',
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
        ]);

        $response = $this->actingAs($admin)
            ->get(route('stamp_correction_request.index', ['status' => 'approved']));

        $response->assertOk();
        $response->assertSee('承認済み');
        $response->assertSee('一般ユーザー');
        $response->assertSee('通院のため');
        $response->assertSee('2026/06/11');
    }

    /** @test */
    public function 管理者は修正申請の詳細内容を確認できる()
    {
        $admin = $this->createAdmin();
        $user = $this->createGeneralUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-12',
            'clock_in_at' => '2026-06-12 09:00:00',
            'clock_out_at' => '2026-06-12 18:00:00',
        ]);

        $request = AttendanceCorrectionRequest::create([
            'attendance_id' => $attendance->id,
            'user_id' => $user->id,
            'requested_clock_in_at' => '2026-06-12 10:00:00',
            'requested_clock_out_at' => '2026-06-12 19:00:00',
            'reason' => '電車遅延',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.stamp_correction_request.show', $request->id));

        $response->assertOk();
        $response->assertSee('一般ユーザー');
        $response->assertSee('2026年');
        $response->assertSee('6月12日');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
        $response->assertSee('電車遅延');
    }
}