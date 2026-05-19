<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 名前が未入力の場合バリデーションメッセージが表示される()
    {
        $response = $this->post('/register/custom', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
        ]);
    }

    /** @test */
public function メールアドレスが未入力の場合バリデーションメッセージが表示される()
{
    $response = $this->post('/register/custom', [
        'name' => 'テスト太郎',
        'email' => '',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $response->assertSessionHasErrors([
        'email' => 'メールアドレスを入力してください',
    ]);
}

/** @test */
public function パスワードが8文字未満の場合バリデーションメッセージが表示される()
{
    $response = $this->post('/register/custom', [
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => 'pass',
        'password_confirmation' => 'pass',
    ]);

    $response->assertSessionHasErrors([
        'password' => 'パスワードは8文字以上で入力してください',
    ]);
}

/** @test */
public function パスワード確認が一致しない場合バリデーションメッセージが表示される()
{
    $response = $this->post('/register/custom', [
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'different',
    ]);

    $response->assertSessionHasErrors([
        'password' => 'パスワードと一致しません',
    ]);
}

/** @test */
public function パスワードが未入力の場合バリデーションメッセージが表示される()
{
    $response = $this->post('/register/custom', [
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => '',
        'password_confirmation' => '',
    ]);

    $response->assertSessionHasErrors([
        'password' => 'パスワードを入力してください',
    ]);
}

/** @test */
public function フォームに内容が入力されている場合ユーザー情報が保存される()
{
    $this->post('/register/custom', [
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'テスト太郎',
        'email' => 'test@example.com',
        'role' => 'general',
    ]);
}
}