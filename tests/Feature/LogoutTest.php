<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutTest extends TestCase
{
    use RefreshDatabase;

    /** 正常系:ログイン中のユーザーがログアウトできる */
    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/login');
        $response->assertSessionHas('success', 'ログアウトしました');
        $this->assertGuest();
    }

    /** 異常系:未ログインのユーザーがログアウトしようとすると、ログイン画面にリダイレクトされる */
    public function test_guest_cannot_logout(): void
    {
        $response = $this->post('/logout');

        $response->assertRedirect('/login');
    }
}
