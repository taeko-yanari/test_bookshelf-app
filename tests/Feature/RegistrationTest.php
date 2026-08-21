<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** 正常系:全項目正しく入力した場合、会員登録が成功する */
    public function test_user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/books');
        $response->assertSessionHas('success', '会員登録しました');
        $this->assertDatabaseHas('users', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
        ]);
        $this->assertAuthenticated();
    }

    /** 異常系:名前が未入力 */
    public function test_registration_fails_when_name_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['email' => 'test@example.com']);
    }

    /** 異常系:名前が21文字以上 */
    public function test_registration_fails_when_name_exceeds_max_length(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('あ', 21),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
        $this->assertGuest();
    }

    /** 境界値:名前がちょうど20文字 → 登録できる */
    public function test_user_can_register_when_name_is_exactly_max_length(): void
    {
        $response = $this->post('/register', [
            'name' => str_repeat('あ', 20),
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionDoesntHaveErrors('name');
        $this->assertAuthenticated();
    }

    /** 異常系:メールアドレスが未入力 */
    public function test_registration_fails_when_email_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** 異常系:メールアドレスの形式が不正 */
    public function test_registration_fails_when_email_format_is_invalid(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'not-an-email',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** 異常系:メールアドレスが256文字以上 */
    public function test_registration_fails_when_email_exceeds_max_length(): void
    {
        $longLocalPart = str_repeat('a', 250);
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => $longLocalPart.'@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /** 異常系:既に登録済みのメールアドレスで登録しようとする */
    public function test_registration_fails_when_email_is_already_registered(): void
    {
        User::factory()->create(['email' => 'test@example.com']);

        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertDatabaseCount('users', 1);
    }

    /** 異常系:パスワードが未入力 */
    public function test_registration_fails_when_password_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** 異常系:パスワードが8文字未満 */
    public function test_registration_fails_when_password_is_too_short(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass1',
            'password_confirmation' => 'pass1',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** 境界値:パスワードがちょうど8文字 → 登録できる */
    public function test_user_can_register_when_password_is_exactly_min_length(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'pass1234',
            'password_confirmation' => 'pass1234',
        ]);

        $response->assertSessionDoesntHaveErrors('password');
        $this->assertAuthenticated();
    }

    /** 異常系:パスワードと確認用パスワードが不一致 */
    public function test_registration_fails_when_password_confirmation_does_not_match(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** 異常系:確認用パスワードが未入力 */
    public function test_registration_fails_when_password_confirmation_is_missing(): void
    {
        $response = $this->post('/register', [
            'name' => 'テスト太郎',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
        $this->assertGuest();
    }

    /** 認証済みユーザーが会員登録画面にアクセスすると、書籍一覧にリダイレクトされる */
    public function test_authenticated_user_is_redirected_from_register_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register');

        $response->assertRedirect('/books');
    }
}
