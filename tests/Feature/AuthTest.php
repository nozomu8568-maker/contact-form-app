<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 登録すると管理画面へ移動する(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertDatabaseHas('users', ['email' => 'taro@example.com']);
        $this->assertAuthenticated();
    }

    /** @test */
    public function 登録は必須項目が空だとエラーメッセージが返る(): void
    {
        $response = $this->post('/register', []);

        $response->assertSessionHasErrors([
            'name' => 'お名前を入力してください',
            'email' => 'メールアドレスを入力してください',
            'password' => 'パスワードを入力してください',
        ]);
    }

    /** @test */
    public function 登録はパスワードが7文字だと拒否される(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'pass123',
            'password_confirmation' => 'pass123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードは8文字以上で入力してください']);
        $this->assertDatabaseMissing('users', ['email' => 'taro@example.com']);
    }

    /** @test */
    public function 登録は確認用パスワードが違うと拒否される(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors(['password' => 'パスワードと一致しません']);
    }

    /** @test */
    public function 登録済みのメールアドレスは拒否される(): void
    {
        User::factory()->create(['email' => 'taro@example.com']);

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'taro@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'このメールアドレスは既に登録されています']);
    }

    /** @test */
    public function 正しい情報でログインすると管理画面へ移動する(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
        ]);

        $response->assertRedirect('/admin');
        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function 間違ったパスワードではログインできない(): void
    {
        $user = User::factory()->create(['password' => bcrypt('password123')]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が登録されていません']);
        $this->assertGuest();
    }

    /** @test */
    public function ログアウトできる(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /** @test */
    public function ログイン済みユーザーがログイン画面を開くと管理画面へ移動する(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/admin');
    }
}
