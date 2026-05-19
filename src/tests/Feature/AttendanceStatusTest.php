<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceStatusTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    use RefreshDatabase;

    /** @test */
    public function 勤務外の場合勤怠ステータスが正しく表示される()
    {
        $user = User::create([
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $user->markEmailAsVerified();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertSee('勤務外');
    }

    /** @test */
public function 出勤中の場合勤怠ステータスが正しく表示される()
{
    $user = User::create([
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'general',
        'email_verified_at' => now(),
    ]);

    $user->markEmailAsVerified();

    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'work_date' => now()->toDateString(),
        'clock_in_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get('/attendance');

    $response->assertSee('出勤中');
}

/** @test */
public function 休憩中の場合勤怠ステータスが正しく表示される()
{
    $user = User::create([
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'general',
        'email_verified_at' => now(),
    ]);

    $user->markEmailAsVerified();

    $attendance = \App\Models\Attendance::create([
        'user_id' => $user->id,
        'work_date' => now()->toDateString(),
        'clock_in_at' => now(),
    ]);

    \App\Models\AttendanceBreak::create([
        'attendance_id' => $attendance->id,
        'break_start_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get('/attendance');

    $response->assertSee('休憩中');
}

/** @test */
public function 退勤済の場合勤怠ステータスが正しく表示される()
{
    $user = User::create([
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'role' => 'general',
        'email_verified_at' => now(),
    ]);

    $user->markEmailAsVerified();

    \App\Models\Attendance::create([
        'user_id' => $user->id,
        'work_date' => now()->toDateString(),
        'clock_in_at' => now()->subHours(8),
        'clock_out_at' => now(),
    ]);

    $response = $this->actingAs($user)
        ->get('/attendance');

    $response->assertSee('退勤済');
}
}
