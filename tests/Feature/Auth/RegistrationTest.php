<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_redirects_to_home(): void
    {
        // Public registration is disabled; admins create users from their profile page.
        $response = $this->get('/register');

        $response->assertRedirect(route('home'));
    }

    public function test_registration_post_redirects_to_home(): void
    {
        // Public registration is disabled; admins create users from their profile page.
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
