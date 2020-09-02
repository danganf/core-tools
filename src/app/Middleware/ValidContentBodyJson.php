<?php

namespace DanganfTools\App\Http\Middleware;

use Illuminate\Support\Facades\App;

class ValidContentBodyJson
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if ( $request->isJson() ) {
            $action = $request->route()->getAction()['as'];
            if( !empty( $action ) ) {

                $tmp = explode('.', $action);

                $nameSpacce = '';
                if( count( $tmp ) > 1 ){
                    $nameSpacce = ucfirst( strtolower( current( $tmp ) ) ) . '\\';
                    unset( $tmp[0] );
                    $action = implode('.', $tmp);
                }

                $action   = ucfirst( camel_case( str_replace('.','_',$action) ) );
                $instacia = App::make("\App\Json\\".$nameSpacce."Json".$action);
                $json     = $request->getContent();
                if ( !empty($json) ) {
                    $instacia->set($json);
                    return $next($request);
                }
            }
        }
        return msgErroJson('Falha nos parametros!');
    }
}
