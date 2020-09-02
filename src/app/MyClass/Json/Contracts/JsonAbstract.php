<?php

namespace IntercaseTools\MyClass\Json\Contracts;

use IntercaseTools\Exceptions\ApiException;

class JsonAbstract
{
    protected $json=null;
    protected $returnGet = '';

    public function setJson($json){ $this->json = $json; }

    private function trataRecursivo($json,$arvore=''){
        foreach( $json AS $key=>$value ) {

            $retorno = null;

            if( is_array( $value ) || is_object( $value ) ) {
                $retorno = $this->trataRecursivo($value,$key);
            } else if ( !empty( $value ) ) {
                $retorno = $value;
            }
            //var_dump($arvore);
            if( empty( $arvore ) )
                $json->{$key} = $retorno;
            else
                $json->{$arvore}->{$key} = $retorno;

        }

        return $json;
    }

    public function isJson($string)
    {
        return ( (is_string($string) && (is_object(json_decode($string)) || is_array(json_decode($string)) )) && !empty($string)  ) ? true : false;
    }

    public function get( $property, $create = null ) {

        $json = $this->json;

        if( is_null($json) ) return FALSE;

        if( !is_object ( $json ) )
            $json = json_decode($json);

        $call = $this->buildCall( $property );

        try {
            $valor = eval ("return ( " . $call . " );");
        } catch (\Exception $e) {
            $valor = null;
        }

        $return = is_null( $valor ) ? $this->returnGet : $valor;

        if( $return  == $this->returnGet ) {
            $this->create( $property, $create );
        }

        return $return;
    }

    public function has($property){

        $json = $this->json;
        if( is_null($json) ) return FALSE;
        return array_has( (array)$json, $property );
    }

    private function buildCall($property){

        $call         = "";
        $property     = explode ( '.', $property );

        // Loop para realizar a chamada encadeada
        for ($i = 0; $i < count ($property); $i++) {
            $call .= $property[$i] . "->";
        }

        $call  = rtrim ($call, "->");
        $call  = "\$json->" . $call;

        return $call;

    }

    public function create( $path, $valor = null ){
        $json = $this->json;
        $call = $this->buildCall( $path );

        @eval ("$call = \$valor;");
        $this->json = $json;
    }

    public function setReturnPadrao($val=null){
        $this->returnGet = $val;
    }

    public function error($message){

        throw new ApiException( $message );
    }

    public function getJson() {
        return $this->json;
    }

    public function toArray(){
        return objectToArray( $this->getJson() );
    }

}
