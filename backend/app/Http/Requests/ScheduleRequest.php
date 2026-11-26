<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['required', 'date'],
            'end_at' => ['required', 'date', 'after:start_at'],
            'status' => ['nullable', Rule::in(Schedule::STATUSES)],
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        // Hanya nama kolom yang dicatat, bukan nilai yang dikirim pengguna.
        Log::warning('validasi jadwal gagal', [
            'event' => 'schedule.validation_failed',
            'user_id' => $this->user()?->id,
            'fields' => array_keys($validator->errors()->toArray()),
        ]);

        parent::failedValidation($validator);
    }
}
