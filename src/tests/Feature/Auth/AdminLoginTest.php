<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/admin/login/custom', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'メールアドレスを入力してください',
        ]);
    }


    /** @test */
    public function パスワードが未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/admin/login/custom', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors([
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function 登録内容と一致しない場合バリデーションメッセージが表示される()
    {
        $this->createAdmin();

        $response = $this->post('/admin/login/custom', [
            'email' => 'admin@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors([
            'email' => 'ログイン情報が登録されていません',
        ]);
    }

    /** @test */
    public function 一般ユーザーは管理者としてログインできない()
    {
        User::create([
            'name' => '一般ユーザー',
            'email' => 'user@example.com',
            'password' => Hash::make('password'),
            'role' => 'general',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login/custom', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();

        $response->assertSessionHasErrors([
            'email' => '管理者権限がありません',
        ]);
    }

    /** @test */
    public function 正しい情報が入力された場合管理者としてログインできる()
    {
        $admin = $this->createAdmin();

        $response = $this->post('/admin/login/custom', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($admin);

        $response->assertRedirect('/admin/attendance/list');
    }

    private function createAdmin()
    {
        return User::create([
            'name' => '管理者',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }
}