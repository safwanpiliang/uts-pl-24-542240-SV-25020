<?php

namespace App\Models;

use Database\Factories\ReminderFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

#[Fillable(['minutes_before'])]
class Reminder extends Model
{
    /** @use HasFactory<ReminderFactory> */
    use HasFactory;

    public const MAX_PER_SCHEDULE = 3;

    public const MAX_MINUTES_BEFORE = 10080;

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class);
    }

    /** Waktu pengingat dihitung dari jadwal, tidak disimpan. */
    public function remindAt(): Carbon
    {
        return $this->schedule->start_at->copy()->subMinutes($this->minutes_before);
    }
}
