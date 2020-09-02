<?php
namespace IntercaseTools\App\MyClass;

use Illuminate\Support\Facades\App;
use IntercaseTools\Exceptions\ApiException;
use IntercaseTools\MyClass\Contracts\InstanceAbstract;
use IntercaseTools\MyClass\JsonBlank;

class RepositoryFactory extends InstanceAbstract {

    public function __call($name, $arguments)
    {
        return $this->execClass( $name . 'Repository' );
    }

    private function execClass( $className, JsonBlank $json=null ){

        $dir = base_path();

        $paths = [
            $dir . '/app/Repositories/Service{VIEW}/'                   => 'App\Repositories\Service{VIEW}',
            $dir . '/app/Repositories/'                                 => 'App\Repositories',
            $dir . '/vendor/intercase/core-tools/src/app/Repositories/' => 'IntercaseTools\Repositories',
        ];

        foreach ($paths as $path => $nameSpace) {

            $viewName = \Request::get('view_name');
            $viewName = !empty( $viewName ) ? $viewName : '';

            $path      = str_replace('{VIEW}', $viewName, $path);
            $nameSpace = str_replace('{VIEW}', $viewName, $nameSpace);

            $className = ucfirst( camel_case( trim( $className ) ) );

            if ( file_exists($path . $className . '.php' ) ) {

                $return = App::make($nameSpace . "\\$className");
                if( $json ) {
                    $return->setJson($json);
                }
                return $return;
            }
        }

        throw new ApiException( "Class {$className} not found" );

    }

}
