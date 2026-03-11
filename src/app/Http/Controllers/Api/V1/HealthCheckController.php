<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = [
            'db' => fn() => $this->checkDb(),
            'cache' => fn() => $this->checkRedis(),
            'queue' => fn() => $this->checkQueue(),
            'storage' => fn() => $this->checkStorage(),
        ];

        $result = [];

        foreach ($checks as $name => $check) {
            try {
                $result[$name] = $check();
            } catch (Throwable $e) {
                $result[$name] = false;
            }
        }

        $status = in_array(false, $result, true) ? 500 : 200;

        return response()->json($result, $status);
    }

    private function checkDb(): bool
    {
        DB::select('SELECT 1');
        return true;
    }

    private function checkRedis(): bool
    {
        Redis::connection()->ping();

        $key = 'healthcheck:test';
        Cache::store('redis')->put($key, 'ok', 10);

        return Cache::store('redis')->get($key) === 'ok';
    }

    private function checkQueue(): bool
    {
        dispatch(function () {})->onQueue('health');
        return true;
    }

    private function checkStorage(): bool
    {
        $disk = Storage::disk('local');

        $disk->put('health.txt', 'ok');

        return $disk->get('health.txt') === 'ok';
    }
}
