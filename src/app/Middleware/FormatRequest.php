<?php

namespace App\Http\Middleware;

use Closure;
use IntercaseTools\MyClass\JsonBlank;

class FormatRequest
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $instance = new JsonBlank();
        $instance->set( json_encode( [ 'params' => $request->all() ] ) );
        $dados = $request->all();
        $dados['json'] = $instance;
        $request->replace($dados);
        return $next($request);

    }
}
