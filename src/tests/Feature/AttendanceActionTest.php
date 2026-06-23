<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceActionTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
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
    public function 出勤ボタンを押すと出勤時刻が保存される()
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->post('/attendance/clock-in');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);
    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = $this->createUser();

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-in');

        $this->assertEquals(
            1,
            Attendance::where('user_id', $user->id)
                ->where('work_date', now()->toDateString())
                ->count()
        );
    }

    /** @test */
    public function 休憩入ボタンを押すと休憩開始時刻が保存される()
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/break-start');

        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);
    }

    /** @test */
    public function 休憩入りボタンが正しく機能する()
    {
        // Arrange: 出勤中のユーザーを用意する
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'status' => '出勤中',
        ]);

        // Act + Assert: 勤怠登録画面に休憩入りボタンが表示されている
        $this->actingAs($user)
            ->post('/attendance')
            ->assertSee('休憩入');

        // Act: 休憩処理を実行する
        $this->actingAs($user)
            ->post('/attendance/break-start');

        // Assert: 休憩開始時刻がDBに保存される
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);

        // Assert: 処理後、ステータスが休憩中になる
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        // Arrange: 出勤中のユーザーを用意する
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'status' => '出勤中',
        ]);

        // Arrange: 1回目の休憩を開始して終了する
        $this->actingAs($user)
            ->post('/attendance/break-start');

        $this->actingAs($user)
            ->post('/attendance/break-end');

        // Act + Assert: 休憩終了後、もう一度「休憩入」ボタンが表示される
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩入');

        // Act: 2回目の休憩を開始する
        $this->actingAs($user)
            ->post('/attendance/break-start');

        // Assert: 休憩レコードが2件保存されている
        $this->assertEquals(
            2,
            AttendanceBreak::where('attendance_id', $attendance->id)->count()
        );
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        // Arrange: 出勤中のユーザーを用意する
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
            'status' => '出勤中',
        ]);

        // Arrange: 休憩中の状態にする
        $this->actingAs($user)
            ->post('/attendance/break-start');

        // Act + Assert: 休憩戻ボタンが表示されている
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('休憩戻');

        // Act: 休憩戻処理を実行する
        $this->actingAs($user)
            ->post('/attendance/break-end');

        // Assert: 休憩終了時刻がDBに保存される
        $this->assertDatabaseHas('attendance_breaks', [
            'attendance_id' => $attendance->id,
        ]);

        $this->assertNotNull(
            AttendanceBreak::where('attendance_id', $attendance->id)
                ->latest()
                ->first()
                ->break_end_at
        );

        // Assert: 処理後、ステータスが出勤中に戻る
        $this->actingAs($user)
            ->get('/attendance')
            ->assertSee('出勤中');
    }

    /** @test */
    public function 退勤ボタンを押すと退勤時刻が保存される()
    {
        $user = $this->createUser();

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'clock_in_at' => now(),
        ]);

        $this->actingAs($user)
            ->post('/attendance/clock-out');

        $this->assertNotNull($attendance->fresh()->clock_out_at);
    }


}
