<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ReminderListDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_owner_lists_reminders_ordered_from_furthest_to_nearest(): void
    {
        $schedule = Schedule::factory()->for($this->user)->create();
        foreach ([10, 1440, 60] as $minutes) {
            Reminder::factory()->for($schedule)->create(['minutes_before' => $minutes]);
        }

        $this->getJson("/api/schedules/{$schedule->id}/reminders")
            ->assertOk()
            ->assertJsonPath('data.0.minutes_before', 1440)
            ->assertJsonPath('data.1.minutes_before', 60)
            ->assertJsonPath('data.2.minutes_before', 10)
            ->assertJsonStructure(['data' => [['id', 'schedule_id', 'minutes_before', 'remind_at', 'created_at']]]);
    }

    public function test_list_only_contains_reminders_of_that_schedule(): void
    {
        $schedule = Schedule::factory()->for($this->user)->create();
        $other = Schedule::factory()->for($this->user)->create();
        Reminder::factory()->for($schedule)->create(['minutes_before' => 15]);
        Reminder::factory()->for($other)->create(['minutes_before' => 45]);

        $this->getJson("/api/schedules/{$schedule->id}/reminders")
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_another_user_gets_404_when_listing_reminders(): void
    {
        $schedule = Schedule::factory()->create();
        Reminder::factory()->for($schedule)->create();

        $this->getJson("/api/schedules/{$schedule->id}/reminders")->assertNotFound();
    }

    public function test_owner_can_delete_a_reminder(): void
    {
        $schedule = Schedule::factory()->for($this->user)->create();
        $reminder = Reminder::factory()->for($schedule)->create();

        $this->deleteJson("/api/schedules/{$schedule->id}/reminders/{$reminder->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Pengingat berhasil dihapus.');

        $this->assertDatabaseMissing('reminders', ['id' => $reminder->id]);
    }

    public function test_another_user_cannot_delete_a_reminder(): void
    {
        $schedule = Schedule::factory()->create();
        $reminder = Reminder::factory()->for($schedule)->create();

        $this->deleteJson("/api/schedules/{$schedule->id}/reminders/{$reminder->id}")->assertNotFound();

        $this->assertDatabaseHas('reminders', ['id' => $reminder->id]);
    }

    public function test_reminder_cannot_be_deleted_through_a_different_schedule_of_the_same_user(): void
    {
        $schedule = Schedule::factory()->for($this->user)->create();
        $other = Schedule::factory()->for($this->user)->create();
        $reminder = Reminder::factory()->for($other)->create();

        $this->deleteJson("/api/schedules/{$schedule->id}/reminders/{$reminder->id}")->assertNotFound();

        $this->assertDatabaseHas('reminders', ['id' => $reminder->id]);
    }
}
