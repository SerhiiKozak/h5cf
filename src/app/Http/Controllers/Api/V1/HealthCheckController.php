<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Throwable;

class HealthCheckController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $checks = ['db', 'cache', 'queue', 'storage'];

        $result = collect($checks)->mapWithKeys(function ($check) {
            $method = 'check' . ucfirst($check);

            try {
                return [$check => method_exists($this, $method) ? $this->$method() : false];
            } catch (Throwable $e) {
                return [$check => false];
            }
        })->toArray();

        $status = in_array(false, $result, true) ? 500 : 200;

        return response()->json($result, $status);
    }

    private function checkDb(): bool
    {
        DB::select('SELECT 1');
        return true;
    }

    private function checkCache(): bool
    {
        try {
            if (Config::get('cache.default') !== 'redis') {  // через різні способи кешування додав перевірку на redis щоб не плодити окремі функції для кешу checkRedis() ...
                return false;
            }
            Redis::connection()->ping();

            $key = 'healthcheck:' . uniqid();

            Cache::store('redis')->put($key, 'ok', 10);

            return Cache::store('redis')->get($key) === 'ok';

        } catch (\Throwable) {
            return false;
        }
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
