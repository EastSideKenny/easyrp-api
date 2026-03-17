<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    private string $frontendUrl;

    protected function setUp(): void
    {
        parent::setUp();
        $this->frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
    }

    public function test_cors_headers_are_present_for_api_requests_from_frontend(): void
    {
        $response = $this->withHeaders([
            'Origin' => $this->frontendUrl,
        ])->getJson('/api/user');

        $response->assertHeader('Access-Control-Allow-Origin', $this->frontendUrl);
    }

    public function test_cors_preflight_request_is_handled(): void
    {
        $response = $this->withHeaders([
            'Origin' => $this->frontendUrl,
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type,Accept',
        ])->options('/api/login');

        $response->assertStatus(204);
        $response->assertHeader('Access-Control-Allow-Origin', $this->frontendUrl);
        $response->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function test_csrf_cookie_endpoint_allows_cors_from_frontend(): void
    {
        $response = $this->withHeaders([
            'Origin' => $this->frontendUrl,
        ])->getJson('/sanctum/csrf-cookie');

        $response->assertHeader('Access-Control-Allow-Origin', $this->frontendUrl);
    }
}
