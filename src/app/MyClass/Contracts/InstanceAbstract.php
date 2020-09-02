<?php

namespace DanganfTools\MyClass\Contracts;

use DanganfTools\MyClass\JsonBlank;

abstract class InstanceAbstract
{
    private $json;

    public function getMethodName( $methodArray ){
        return $this->getClassName( $methodArray );
    }
    public function getClassName( $moduleArray ){
        $className = false;
        if( is_array( $moduleArray ) ) {
            $className = strtolower(key($moduleArray));
            $className = ucfirst(camel_case(snake_case($className)));
        }
        return $className;
    }

    public function getJson(){return $this->json;}

    public function setJson( JsonBlank $json ){
        $this->json = $json;
        return $this;
    }

    public function callMethod( $scope, $method, $arqs = [] ){
        $return = null;
        if (method_exists($scope, $method)) {
            $return  = call_user_func_array( [ $this, $method ], $arqs );
        }
        return $return;
    }
}
