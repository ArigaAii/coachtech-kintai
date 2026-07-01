<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAttendanceListTest extends TestCase
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

    private function createUser($name, $email)
    {
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $user->markEmailAsVerified();

        return $user;
    }

    /** @test */
    public function その日の全ユーザーの勤怠情報が正確に表示される()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 10:00:00'));

        $admin = $this->createAdmin();

        $userA = $this->createUser('山田 太郎', 'taro@example.com');
        $userB = $this->createUser('佐藤 花子', 'hanako@example.com');

        Attendance::create([
            'user_id' => $userA->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 09:00:00',
            'clock_out_at' => '2026-06-22 18:00:00',
            'status' => '退勤済',
        ]);

        Attendance::create([
            'user_id' => $userB->id,
            'work_date' => '2026-06-22',
            'clock_in_at' => '2026-06-22 10:00:00',
            'clock_out_at' => '2026-06-22 19:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index'));

        $response->assertOk();

        $response->assertSee('山田 太郎');
        $response->assertSee('佐藤 花子');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }

    /** @test */
    public function 勤怠一覧画面に現在の日付が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 10:00:00'));

        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index'));

        $response->assertOk();
        $response->assertSee('2026/06/22');

        Carbon::setTestNow();
    }

    /** @test */
    public function 前日ボタンを押すと前日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 10:00:00'));

        $admin = $this->createAdmin();

        $user = $this->createUser('山田 太郎', 'taro@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-21',
            'clock_in_at' => '2026-06-21 08:30:00',
            'clock_out_at' => '2026-06-21 17:30:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['date' => '2026-06-21']));

        $response->assertOk();
        $response->assertSee('2026/06/21');
        $response->assertSee('山田 太郎');
        $response->assertSee('08:30');
        $response->assertSee('17:30');

        Carbon::setTestNow();
    }

    /** @test */
    public function 翌日のボタンを押すと翌日の勤怠情報が表示される()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-22 10:00:00'));

        $admin = $this->createAdmin();

        $user = $this->createUser('佐藤 花子', 'hanako@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-23',
            'clock_in_at' => '2026--6-23 10:00:00',
            'clock_out_at' => '2026-06-23 19:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.index', ['date' => '2026-06-23']));

        $response->assertOk();
        $response->assertSee('2026/06/23');
        $response->assertSee('佐藤 花子');
        $response->assertSee('10:00');
        $response->assertSee('19:00');

        Carbon::setTestNow();
    }
}