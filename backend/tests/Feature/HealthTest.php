<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class HealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_returns_200_when_database_and_cache_are_up(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertExactJson([
                'status' => 'healthy',
                'checks' => ['database' => 'up', 'cache' => 'up'],
            ]);
    }

    public function test_health_returns_503_and_names_the_failing_part_when_database_is_down(): void
    {
        Log::spy();
        DB::shouldReceive('select')->andThrow(new \RuntimeException('koneksi ditolak'));

        $this->getJson('/health')
            ->assertStatus(503)
            ->assertExactJson([
                'status' => 'unhealthy',
                'checks' => ['database' => 'down', 'cache' => 'up'],
            ]);

        Log::shouldHaveReceived('error')->once();
    }
}
