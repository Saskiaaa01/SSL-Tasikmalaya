<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function home_page_is_accessible()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }
}

