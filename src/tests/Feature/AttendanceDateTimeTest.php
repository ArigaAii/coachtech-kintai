<?php

namespace Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceDateTimeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 現在の日時情報がuiと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::parse('2026-06-08 14:35:00'));

        $user = User::create([
            'name' => 'テスト太朗',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
            'role' =>'general',
            'email_verified_at' => now(),
        ]);

        $user->markEmailAsVerified();

        $response = $this->actingAs($user)
            ->get('/attendance');

        $response->assertOk();
        $response->assertSee('2026年6月8日(月)');
        $response->assertSee('14:35');

        Carbon::setTestNow();
    }
}