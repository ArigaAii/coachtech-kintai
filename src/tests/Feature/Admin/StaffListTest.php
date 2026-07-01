<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Attendance;

class StaffListTest extends TestCase
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
    public function 管理者が全一般ユーザーの氏名とメールアドレスを確認できる()
    {
        $admin = $this->createAdmin();

        $userA = $this->createUser('山田 太郎', 'taro@example.com');
        $userB = $this->createUser('佐藤 花子', 'hanako@example.com');

        $response = $this->actingAs($admin)
            ->get(route('admin.staff.index'));

        $response->assertOk();

        $response->assertSee($userA->name);
        $response->assertSee($userA->email);
        $response->assertSee($userB->name);
        $response->assertSee($userB->email);

        $response->assertDontSee($admin->email);
    }

    /** @test */
    public function 選択したユーザーの勤怠情報が正しく表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('山田 太郎', 'taro@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-10',
            'clock_in_at' => '2026-06-10 09:00:00',
            'clock_out_at' => '2026-06-10 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff', [
                'user' => $user->id,
                'month' => '2026-06',
            ]));

        $response->assertOk();
        $response->assertSee('山田 太郎');
        $response->assertSee('06/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 前月ボタンを押すと前月の勤怠情報が表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('山田 太郎', 'taro@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-05-10',
            'clock_in_at' => '2026-05-10 09:00:00',
            'clock_out_at' => '2026-05-10 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff', [
                'user' => $user->id,
                'month' => '2026-05',
            ]));

        $response->assertOk();
        $response->assertSee('2026/05');
        $response->assertSee('05/10');
        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 翌月ボタンを押すと翌月の勤怠情報が表示される()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('佐藤 花子', 'hanako@example.com');

        Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-07-10',
            'clock_in_at' => '2026-07-10 10:00:00',
            'clock_out_at' => '2026-07-10 19:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff', [
                'user' => $user->id,
                'month' => '2026-07',
            ]));

        $response->assertOk();
        $response->assertSee('2026/07');
        $response->assertSee('07/10');
        $response->assertSee('10:00');
        $response->assertSee('19:00');
    }

    /** @test */
    public function 詳細ボタンを押すとその日の勤怠詳細画面に遷移する()
    {
        $admin = $this->createAdmin();
        $user = $this->createUser('山田 太郎', 'taro@example.com');

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'work_date' => '2026-06-10',
            'clock_in_at' => '2026-06-10 09:00:00',
            'clock_out_at' => '2026-06-10 18:00:00',
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.staff', [
                'user' => $user->id,
                'month' => '2026-06',
            ]));

        $response->assertOk();
        $response->assertSee('詳細');
        $response->assertSee(route('admin.attendance.show', $attendance->id), false);

        $detailResponse = $this->actingAs($admin)
            ->get(route('admin.attendance.show', $attendance->id));

        $detailResponse->assertOk();
        $detailResponse->assertSee('勤怠詳細');
        $detailResponse->assertSee('山田');
        $detailResponse->assertSee('太郎');
    }
}