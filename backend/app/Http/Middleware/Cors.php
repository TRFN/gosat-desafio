<?php

namespace App\Http\Middleware;

use Closure;

class Cors
{
	/**
	 * Manipula as requisições para adicionar os cabeçalhos CORS.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  \Closure  $next
	 * @return mixed
	 */
	public function handle($request, Closure $next)
	{
		$response = $next($request);

		$headers = [
			'Access-Control-Allow-Origin'      => '*',
			'Access-Control-Allow-Methods'     => 'GET, POST',
			'Access-Control-Allow-Headers'     => 'Content-Type',
		];

		foreach ($headers as $key => $value) {
			$response->headers->set($key, $value);
		}

		return $response;
	}
}
