<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceDetailTest extends TestCase
{
    use RefreshDatabase;

    private function createAdmin()
    {
        return User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    private function createUser()
    {
        $user = User::create([
            'name' => '山田 太郎',
            'email' => 'taro@example.com',
            'password' => bcrypt('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $user->markEmailAsVerified();

        return $user;
    }

    /** @test */
    public function 勤怠詳細画面に選択した勤怠情報が表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 09:00:00',
            'clock_out_at' => '2026-06-22 18:00:00',
            'status' => '退勤済',
            'note' => '通常勤務',
        ]);

        AttendanceBreak::create([
            'attendance_id' => $attendance->id,
            'break_start_at' => '2026-06-22 12:00:00',
            'break_end_at' => '2026-06-22 13:00:00',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance->id));

        $response->assertOk();
        $response->assertSee('山田');
        $response->assertSee('太郎');
        $response->assertSee('2026年');
        $response->assertSee('6月22日');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
        $response->assertSee('通常勤務');
    }

    /** @test */
    public function 出勤時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 09:00:00',
            'clock_out_at' => '2026-06-22 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in_at' => '19:00',
                'clock_out_at' => '18:00',
                'break_start_at' => [
                    '12:00',
                ],
                'break_end_at' => [
                    '13:00',
                ],
                'note' => '出勤時間を修正',
            ]);

        $response->assertSessionHasErrors([
            'clock_in_at' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 09:00:00',
            'clock_out_at' => '2026-06-22 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'break_start_at' => [
                    '19:00',
                ],
                'break_end_at' => [
                    '20:00',
                ],
                'note' => '休憩時間を修正',
            ]);

        $response->assertSessionHasErrors([
            'break_start_at.0' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 09:00:00',
            'clock_out_at' => '2026-06-22 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update', $attendance->id), [
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'break_start_at' => [
                    '12:00',
                ],
                'break_end_at' => [
                    '19:00',
                ],
                'note' => '休憩終了時間を修正',
            ]);

        $response->assertSessionHasErrors([
            'break_end_at.0' => '休憩時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser();

        $attendance =Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 09:00:00',
            'clock_out_at' => '2026-06-22 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.attendance.update',$attendance->id),[
                'clock_in_at' => '09:00',
                'clock_out_at' => '18:00',
                'break_start_at' => [
                    '12:00',
                ],
                'break_end_at' => [
                    '13:00',
                ],
                'note' => '',
            ]);

        $response->assertSessionHasErrors([
            'note' => '備考を記入してください',
        ]);
    }
}