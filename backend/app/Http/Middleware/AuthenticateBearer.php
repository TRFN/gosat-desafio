<?php
// app/Http/Middleware/AuthenticateBearer.php

namespace App\Http\Middleware;

use Closure;

class AuthenticateBearer
{
	public function handle($request, Closure $next)
	{
		$authorization = $request->header('Authorization');

		if (!$authorization || !preg_match('/Bearer\s(\S+)/', $authorization, $matches)) {
			return response()->json(['error' => 'Unauthorized'], 401);
		}

		$token = $matches[1];

		$validToken = env('API_BEARER_TOKEN', ''); // Token definido no .env

		if (empty($validToken)) {
			return response()->json(['error' => 'Bearer token not configured'], 500);
		}

		if ($token !== $validToken) {
			return response()->json(['error' => 'Invalid token'], 401);
		}

		return $next($request);
	}
}
