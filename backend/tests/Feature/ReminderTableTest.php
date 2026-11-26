<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\Schedule;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReminderTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_rejects_duplicate_minutes_before_on_the_same_schedule(): void
    {
        $schedule = Schedule::factory()->create();
        Reminder::factory()->for($schedule)->create(['minutes_before' => 30]);

        $this->expectException(UniqueConstraintViolationException::class);

        Reminder::factory()->for($schedule)->create(['minutes_before' => 30]);
    }

    public function test_same_minutes_before_is_allowed_on_different_schedules(): void
    {
        Reminder::factory()->create(['minutes_before' => 30]);
        Reminder::factory()->create(['minutes_before' => 30]);

        $this->assertDatabaseCount('reminders', 2);
    }

    public function test_deleting_a_schedule_deletes_its_reminders(): void
    {
        $schedule = Schedule::factory()->create();
        Reminder::factory()->for($schedule)->create(['minutes_before' => 10]);
        Reminder::factory()->for($schedule)->create(['minutes_before' => 60]);

        $schedule->delete();

        $this->assertDatabaseCount('reminders', 0);
    }
}
