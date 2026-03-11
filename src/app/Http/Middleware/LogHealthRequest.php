<?php

namespace App\Http\Middleware;

use App\Models\HealthCheckLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class LogHealthRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        try {
            $json = json_decode($response->getContent(), true);

            if (!is_array($json)) {
                $json = [];
            }

            $overallOk = !in_array(false, $json, true);

            HealthCheckLog::create([
                'owner_uuid' => $request->header('X-Owner'),
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'overall_ok' => $overallOk,
                'checks' => is_array($json) ? $json : null,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        } catch (Throwable $e) {
            report($e);
        }

        return $response;
    }
}
