<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureOwnerHeader
{
    public function handle(Request $request, Closure $next): Response
    {
        $owner = $request->header('X-Owner');

        if (!$owner || !preg_match(
                '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[1-5][0-9a-fA-F]{3}-[89abAB][0-9a-fA-F]{3}-[0-9a-fA-F]{12}$/',
                $owner
            )) {
            return response()->json([
                'message' => 'Missing or invalid X-Owner header',
            ], 400);
        }

        return $next($request);
    }
}
