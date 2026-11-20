<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
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

    public function test_login_from_a_protected_referrer_opens_on_home_without_losing_the_return_target(): void
    {
        $this->withHeader('referer', route('profile.addresses'))
            ->get(route('login'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('auth_modal', 'login')
            ->assertSessionHas('auth_return_to', '/profile/addresses')
            ->assertSessionHas('url.intended', '/profile/addresses');
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

    public function test_login_prefers_the_current_modal_page_over_a_stale_intended_url(): void
    {
        $user = User::factory()->create();

        $this->withSession(['url.intended' => '/cart'])
            ->post(route('login'), [
                'email' => $user->email,
                'password' => 'password',
                '_auth_return_to' => '/',
            ])
            ->assertRedirect('/');
    }

    public function test_registration_returns_to_the_page_where_its_modal_was_opened(): void
    {
        Notification::fake();

        $this->withSession(['url.intended' => '/cart'])
            ->post(route('register'), [
                'name' => 'Test',
                'gender' => 'male',
                'phone' => '+375291234567',
                'email' => 'registration-' . uniqid() . '@example.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
                '_auth_return_to' => '/',
            ])
            ->assertRedirect('/')
            ->assertSessionHas('auth_modal', 'verify-email');
    }

    public function test_duplicate_registration_email_is_marked_as_a_server_validation_error(): void
    {
        $existingUser = User::factory()->create();

        $this->from(route('home'))
            ->post(route('register'), [
                'name' => 'Test',
                'gender' => 'male',
                'phone' => '+375291234567',
                'email' => $existingUser->email,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                '_auth_modal' => 'register',
                '_auth_return_to' => '/',
            ])
            ->assertRedirect(route('home'))
            ->assertSessionHasErrors(['email']);

        $this->get(route('home'))
            ->assertSee('id="auth-register-email"', false)
            ->assertSee('data-auth-server-invalid', false)
            ->assertSee('data-auth-submit', false);
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

        $response = $this->withHeader('referer', route('profile.addresses'))
            ->actingAs($user)
            ->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
