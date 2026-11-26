<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class HealthController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'database' => $this->check('database', function () {
                DB::select('select 1');
            }),
            'cache' => $this->check('cache', function () {
                Cache::put('health-check', 'ok', 10);

                if (Cache::get('health-check') !== 'ok') {
                    throw new \RuntimeException('nilai cache tidak terbaca kembali');
                }
            }),
        ];

        $healthy = collect($checks)->every(fn (string $status) => $status === 'up');

        return response()->json([
            'status' => $healthy ? 'healthy' : 'unhealthy',
            'checks' => $checks,
        ], $healthy ? 200 : 503);
    }

    private function check(string $name, callable $probe): string
    {
        try {
            $probe();

            return 'up';
        } catch (Throwable $e) {
            Log::error("health check gagal: {$name} tidak dapat diakses", [
                'event' => 'health.check_failed',
                'component' => $name,
                'exception' => class_basename($e),
                'reason' => Str::limit($e->getMessage(), 160),
            ]);

            return 'down';
        }
    }
}
