<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InertiaIntegrationTest extends TestCase
{
    /**
     * Test that home route returns a successful response.
     */
    public function test_home_route_returns_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    /**
     * Test that the response contains Inertia page data.
     */
    public function test_response_contains_inertia_data(): void
    {
        $response = $this->get('/', [
            'X-Inertia' => 'true',
            'X-Inertia-Version' => '1.0',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'component' => 'Welcome',
        ]);
    }
}
