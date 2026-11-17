<?php

namespace Tests\Feature;

use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    public function test_login_form_remembers_same_site_referrer_for_post_login_redirect(): void
    {
        $destination = url('/catalog/example?tab=reviews');

        $response = $this->withHeader('referer', $destination)
            ->get(route('login'));

        $response->assertOk();
        $this->assertSame('/catalog/example?tab=reviews', session('url.intended'));
    }

    public function test_login_form_does_not_store_an_external_referrer_as_intended_url(): void
    {
        $response = $this->withHeader('referer', 'https://external.example/redirect')
            ->get(route('login'));

        $response->assertOk();
        $response->assertSessionMissing('url.intended');
    }
}
