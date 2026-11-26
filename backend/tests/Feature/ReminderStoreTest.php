<?php

namespace Tests\Feature;

use App\Models\Reminder;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ReminderStoreTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-11-26 10:00:00');

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    private function scheduleStartingIn(string $interval): Schedule
    {
        $start = Carbon::now()->add($interval);

        return Schedule::factory()->for($this->user)->create([
            'start_at' => $start,
            'end_at' => $start->copy()->addHour(),
        ]);
    }

    /** AC 1 */
    public function test_owner_can_add_a_reminder_and_remind_at_is_computed(): void
    {
        $schedule = $this->scheduleStartingIn('1 day');

        $response = $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 30]);

        $response->assertCreated()->assertJsonPath('data.minutes_before', 30);
        $this->assertSame(
            $schedule->start_at->copy()->subMinutes(30)->toIso8601String(),
            $response->json('data.remind_at'),
        );
    }

    /** AC 2 */
    public function test_fourth_reminder_on_one_schedule_is_rejected_with_422(): void
    {
        $schedule = $this->scheduleStartingIn('1 day');
        foreach ([10, 20, 30] as $minutes) {
            Reminder::factory()->for($schedule)->create(['minutes_before' => $minutes]);
        }

        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 40])
            ->assertStatus(422)
            ->assertJsonValidationErrors('minutes_before');

        $this->assertSame(3, $schedule->reminders()->count());
    }

    /** AC 3 */
    public function test_duplicate_minutes_before_on_the_same_schedule_is_rejected_with_422(): void
    {
        $schedule = $this->scheduleStartingIn('1 day');
        Reminder::factory()->for($schedule)->create(['minutes_before' => 30]);

        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 30])
            ->assertStatus(422)
            ->assertJsonValidationErrors('minutes_before');

        $this->assertSame(1, $schedule->reminders()->count());
    }

    /** AC 4 */
    public function test_reminder_whose_remind_at_is_already_past_is_rejected_with_422(): void
    {
        $schedule = $this->scheduleStartingIn('10 minutes');

        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 30])
            ->assertStatus(422)
            ->assertJsonValidationErrors('minutes_before');

        $this->assertSame(0, $schedule->reminders()->count());
    }

    /** AC 5 */
    #[DataProvider('invalidMinutes')]
    public function test_minutes_before_outside_1_to_10080_or_not_an_integer_is_rejected_with_422(mixed $value): void
    {
        $schedule = $this->scheduleStartingIn('30 days');

        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => $value])
            ->assertStatus(422)
            ->assertJsonValidationErrors('minutes_before');
    }

    /**
     * @return array<string, array{0: mixed}>
     */
    public static function invalidMinutes(): array
    {
        return [
            'nol' => [0],
            'negatif' => [-5],
            'lebih dari 7 hari' => [10081],
            'desimal' => ['30.5'],
            'bukan angka' => ['abc'],
            'kosong' => [null],
        ];
    }

    /** AC 5, batas yang masih sah */
    public function test_boundary_values_1_and_10080_are_accepted(): void
    {
        $schedule = $this->scheduleStartingIn('30 days');

        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 1])->assertCreated();
        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 10080])->assertCreated();
    }

    /** AC 6 */
    public function test_another_user_cannot_add_a_reminder_to_a_schedule_they_do_not_own(): void
    {
        $schedule = $this->scheduleStartingIn('1 day');
        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 30])->assertCreated();

        Sanctum::actingAs(User::factory()->create());

        $this->postJson("/api/schedules/{$schedule->id}/reminders", ['minutes_before' => 40])->assertNotFound();

        $this->assertSame(1, $schedule->reminders()->count());
    }
}
