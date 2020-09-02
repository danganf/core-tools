<?php

namespace IntercaseTools\MyClass;

use IntercaseTools\MyClass\Json\Contracts\JsonAbstract;
use IntercaseTools\MyClass\Json\Contracts\JsonInterface;

class JsonBlank extends JsonAbstract implements JsonInterface
{
    public function set( $stringJson ) {

        $this->setReturnPadrao();
        $this->setJson( json_decode( $stringJson ) );
        $this->trataDados();

    }

    private function trataDados() {
        //
    }

    public function validRequiredFields( $array ) {
        return TRUE;
    }
}