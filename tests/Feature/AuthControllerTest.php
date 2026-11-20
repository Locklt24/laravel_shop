<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthControllerTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_can_open_registration_page_from_navigation(): void
    {
        $response = $this->get(route('register'));

        $response->assertSeeText('Tạo tài khoản mới')
            ->assertSeeText('Họ và tên')
            ->assertSeeText('Địa chỉ email')
            ->assertSeeText('Mật khẩu')
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('href="'.route('login').'"', false)
            ->assertSee('action="'.route('register.store').'"', false)
            ->assertSee('name="password_confirmation"', false);
    }

    public function test_guest_can_open_login_page_with_a_link_to_registration(): void
    {
        $response = $this->get(route('login'));

        $response->assertSeeText('Chào mừng quay lại')
            ->assertSeeText('Địa chỉ email')
            ->assertSeeText('Mật khẩu')
            ->assertSeeText('Chưa có tài khoản?')
            ->assertSee('href="'.route('register').'"', false)
            ->assertSee('action="'.route('login.store').'"', false)
            ->assertSee('autocomplete="current-password"', false);
    }

    public function test_guest_can_request_a_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'customer@example.com']);

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertRedirect()
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_password_reset_link_request_does_not_reveal_an_unknown_email(): void
    {
        Notification::fake();

        $this->post(route('password.email'), ['email' => 'missing@example.com'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Nếu email tồn tại trong hệ thống, liên kết đặt lại mật khẩu đã được gửi.');

        Notification::assertNothingSent();
    }

    public function test_guest_can_reset_password_with_a_valid_token(): void
    {
        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('Old-password-123'),
        ]);
        $token = Password::createToken($user);

        $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'New-password-123',
            'password_confirmation' => 'New-password-123',
        ])
            ->assertRedirectToRoute('login')
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('New-password-123', $user->fresh()->password));
        $this->assertFalse(Hash::check('Old-password-123', $user->fresh()->password));
    }

    public function test_authenticated_user_can_change_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Old-password-123'),
        ]);

        $this->actingAs($user)
            ->put(route('password.change.update'), [
                'current_password' => 'Old-password-123',
                'password' => 'New-password-123',
                'password_confirmation' => 'New-password-123',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('New-password-123', $user->fresh()->password));
    }

    public function test_change_password_rejects_incorrect_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('Old-password-123'),
        ]);

        $this->actingAs($user)
            ->put(route('password.change.update'), [
                'current_password' => 'Wrong-password-123',
                'password' => 'New-password-123',
                'password_confirmation' => 'New-password-123',
            ])
            ->assertRedirect()
            ->assertSessionHasErrors(['current_password']);

        $this->assertTrue(Hash::check('Old-password-123', $user->fresh()->password));
    }
}
