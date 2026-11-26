<?php

namespace App\Http\Resources;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Reminder
 */
class ReminderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'schedule_id' => $this->schedule_id,
            'minutes_before' => $this->minutes_before,
            'remind_at' => $this->remindAt()->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
