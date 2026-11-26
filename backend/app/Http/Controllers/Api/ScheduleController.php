<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleRequest;
use App\Http\Resources\ScheduleResource;
use App\Models\Schedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Log;

class ScheduleController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $schedules = $request->user()->schedules()->orderBy('start_at')->get();

        return ScheduleResource::collection($schedules);
    }

    public function store(ScheduleRequest $request): JsonResponse
    {
        $schedule = $request->user()->schedules()->create($request->validated());

        Log::info('jadwal dibuat', [
            'event' => 'schedule.created',
            'user_id' => $request->user()->id,
            'schedule_id' => $schedule->id,
        ]);

        return (new ScheduleResource($schedule->refresh()))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, int $id): ScheduleResource
    {
        return new ScheduleResource($this->findOwned($request, $id));
    }

    public function update(ScheduleRequest $request, int $id): ScheduleResource
    {
        $schedule = $this->findOwned($request, $id);
        $schedule->update($request->validated());

        Log::info('jadwal diperbarui', [
            'event' => 'schedule.updated',
            'user_id' => $request->user()->id,
            'schedule_id' => $schedule->id,
        ]);

        return new ScheduleResource($schedule);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $this->findOwned($request, $id)->delete();

        Log::info('jadwal dihapus', [
            'event' => 'schedule.deleted',
            'user_id' => $request->user()->id,
            'schedule_id' => $id,
        ]);

        return response()->json(['message' => 'Jadwal berhasil dihapus.']);
    }

    private function findOwned(Request $request, int $id): Schedule
    {
        return $request->user()->schedules()->findOrFail($id);
    }
}
