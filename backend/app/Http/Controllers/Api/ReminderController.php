<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReminderResource;
use App\Models\Reminder;
use App\Models\Schedule;
use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ReminderController extends Controller
{
    public function store(Request $request, int $schedule): JsonResponse
    {
        // Jadwal milik orang lain dijawab 404 lebih dulu, sebelum validasi.
        $schedule = $request->user()->schedules()->findOrFail($schedule);

        $data = $request->validate([
            'minutes_before' => [
                'bail',
                'required',
                'integer',
                'min:1',
                'max:'.Reminder::MAX_MINUTES_BEFORE,
                Rule::unique('reminders')->where('schedule_id', $schedule->id),
                $this->mustStillBeInTheFuture($schedule),
            ],
        ]);

        $reminder = $this->createWithinLimit($schedule, (int) $data['minutes_before']);

        return (new ReminderResource($reminder))->response()->setStatusCode(201);
    }

    private function mustStillBeInTheFuture(Schedule $schedule): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail) use ($schedule): void {
            $remindAt = $schedule->start_at->copy()->subMinutes((int) $value);

            if (! $remindAt->isFuture()) {
                $fail('Waktu pengingat sudah lewat.');
            }
        };
    }

    /**
     * Batas dicek di dalam transaksi dengan mengunci baris jadwal, supaya dua
     * permintaan bersamaan tidak sama-sama melihat "baru 2 pengingat".
     */
    private function createWithinLimit(Schedule $schedule, int $minutesBefore): Reminder
    {
        try {
            return DB::transaction(function () use ($schedule, $minutesBefore) {
                Schedule::query()->whereKey($schedule->id)->lockForUpdate()->first();

                if ($schedule->reminders()->count() >= Reminder::MAX_PER_SCHEDULE) {
                    throw ValidationException::withMessages([
                        'minutes_before' => 'Jadwal ini sudah memiliki '.Reminder::MAX_PER_SCHEDULE.' pengingat, batas maksimal.',
                    ]);
                }

                $reminder = $schedule->reminders()->create(['minutes_before' => $minutesBefore]);
                $reminder->setRelation('schedule', $schedule);

                return $reminder;
            });
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages([
                'minutes_before' => 'Pengingat dengan waktu ini sudah ada.',
            ]);
        }
    }
}
