<?php

namespace Tests\Feature;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function payload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Kuliah KEPL',
            'description' => 'Membahas CI/CD',
            'location' => 'Ruang 301',
            'start_at' => '2026-10-20 08:00:00',
            'end_at' => '2026-10-20 09:30:00',
        ], $overrides);
    }

    public function test_user_can_list_only_own_schedules(): void
    {
        $user = User::factory()->create();
        Schedule::factory(2)->for($user)->create();
        Schedule::factory(3)->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/schedules')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_create_schedule_returns_201_and_is_stored(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/schedules', $this->payload())
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'Kuliah KEPL')
            ->assertJsonPath('data.status', 'planned');

        $this->assertDatabaseHas('schedules', ['user_id' => $user->id, 'title' => 'Kuliah KEPL']);
    }

    public function test_create_schedule_with_invalid_data_returns_422(): void
    {
        Sanctum::actingAs(User::factory()->create());

        $this->postJson('/api/schedules', $this->payload([
            'title' => '',
            'end_at' => '2026-10-20 07:00:00',
        ]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'end_at']);
    }

    public function test_user_can_show_update_and_delete_schedule(): void
    {
        $user = User::factory()->create();
        $schedule = Schedule::factory()->for($user)->create();
        Sanctum::actingAs($user);

        $this->getJson("/api/schedules/{$schedule->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $schedule->id);

        $this->putJson("/api/schedules/{$schedule->id}", $this->payload(['status' => 'done']))
            ->assertOk()
            ->assertJsonPath('data.status', 'done');

        $this->deleteJson("/api/schedules/{$schedule->id}")->assertOk();
        $this->assertDatabaseMissing('schedules', ['id' => $schedule->id]);
    }

    public function test_user_cannot_access_another_users_schedule(): void
    {
        $other = Schedule::factory()->create();
        Sanctum::actingAs(User::factory()->create());

        $this->getJson("/api/schedules/{$other->id}")->assertStatus(404);
        $this->deleteJson("/api/schedules/{$other->id}")->assertStatus(404);
    }
}
