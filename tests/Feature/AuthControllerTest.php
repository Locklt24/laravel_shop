<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthControllerTest extends TestCase
{
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
}
