<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 正常系:メール・パスワードが正しい場合、ログインが成功する */
    public function test_user_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('success', 'ログインしました');
        $this->assertAuthenticatedAs($user);
    }

    /** 異常系:メールアドレスが未入力 */
    public function test_login_fails_when_email_is_missing(): void
    {
        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** 異常系:パスワードが未入力 */
    public function test_login_fails_when_password_is_missing(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** 異常系:メールアドレスの形式が不正 */
    public function test_login_fails_when_email_format_is_invalid(): void
    {
        $response = $this->post('/login', [
            'email' => 'not-an-email',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** 異常系:メールアドレスは存在するがパスワードが違う */
    public function test_login_fails_when_password_is_incorrect(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が誤っています']);
        $this->assertGuest();
    }

    /** 異常系:メールアドレスが存在しない(未登録) */
    public function test_login_fails_when_email_does_not_exist(): void
    {
        $response = $this->post('/login', [
            'email' => 'notfound@example.com',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors(['email' => 'ログイン情報が誤っています']);
        $this->assertGuest();
    }

    /** レート制限:同一メール・同一IPで6回連続失敗すると、試行回数制限にかかる */
    public function test_login_is_throttled_after_too_many_attempts(): void
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        // 5回失敗させる(制限ちょうどまでは通常のエラー)
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // 6回目でスロットル(試行回数制限)にかかる
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
        $this->assertGuest();
    }

    /** 認証済みユーザーがログイン画面にアクセスすると、書籍一覧にリダイレクトされる */
    public function test_authenticated_user_is_redirected_from_login_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/login');

        $response->assertRedirect('/books');
    }
}
