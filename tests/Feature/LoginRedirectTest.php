<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    public function test_login_form_remembers_same_site_referrer_for_post_login_redirect(): void
    {
        $destination = url('/catalog/example?tab=reviews');

        $response = $this->withHeader('referer', $destination)
            ->get(route('login'));

        $response->assertRedirect($destination);
        $response->assertSessionHas('auth_modal', 'login');
        $this->assertSame('/catalog/example?tab=reviews', session('url.intended'));
    }

    public function test_login_form_does_not_store_an_external_referrer_as_intended_url(): void
    {
        $response = $this->withHeader('referer', 'https://external.example/redirect')
            ->get(route('login'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('auth_modal', 'login');
        $response->assertSessionMissing('url.intended');
    }

    public function test_registration_route_opens_the_registration_modal_on_the_home_page(): void
    {
        $this->get(route('register'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('auth_modal', 'register');
    }

    public function test_login_validation_returns_to_the_page_and_reopens_the_login_modal(): void
    {
        $this->from(route('home'))
            ->post(route('login'), [
                '_auth_modal' => 'login',
                '_auth_return_to' => '/',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors(['email', 'password'])
            ->assertSessionHas('_old_input._auth_modal', 'login');
    }

    public function test_password_reset_link_opens_the_reset_modal_with_its_token(): void
    {
        $this->get(route('password.reset', [
            'token' => 'sample-reset-token',
            'email' => 'customer@example.com',
        ]))
            ->assertRedirect(route('home'))
            ->assertSessionHas('auth_modal', 'reset-password')
            ->assertSessionHas('auth_modal_token', 'sample-reset-token')
            ->assertSessionHas('auth_modal_email', 'customer@example.com');
    }

    public function test_unverified_user_can_log_out(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->withHeader('referer', route('home'))
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect();
        $this->assertGuest();
    }
}
