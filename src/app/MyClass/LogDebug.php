<?php

namespace DanganfTools\MyClass;

use Monolog\Logger,
    Monolog\Handler\StreamHandler,
    Monolog\Handler\FirePHPHandler;

class LogDebug
{

    private $arquivo = 'logdebug';
    private $type    = 'INFO';
    private $prefx   = '';
    private $setup   = [];

    public function criar( $message='start', $setup = array() ) {
        if( config('moddefault.logdebug_on') === TRUE ) {
            $logger = new Logger('[' . \Route::currentRouteName() . ']');
            $handler = new StreamHandler(storage_path() . '/logs/' . $this->arquivo . '.log', Logger::DEBUG);
            $logger->pushHandler($handler);
            $logger->pushHandler(new FirePHPHandler());

            if ($message == 'start') $message = '======#INICIANDO#=======';
            if ($message == 'end') $message = '======#FIM#=======';

            $this->setup = $setup;

            self::criaPorType($logger, $message);
        }
    }

    public function __call($name, $arguments)
    {
        $name  = strtoupper( $name );
        $prefx = '';
        if( array_key_exists( $name, Logger::getLevels()  ) ){
            $this->type = $name;
        } else {
            $prefx = $name;
        }

        $this->prefx = str_pad( $prefx , 12 , ".");
        $this->criar($arguments[0], isset( $arguments[1] ) ? $arguments[1] : [] );

    }

    public function setLogFile($name){
        $this->arquivo = $name . '_';return $this;
    }

    private function criaPorType( $objLogger, $message ) {

        $message = $this->prefx . self::trataEntrada( $message );

        switch ( $this->type ){

            case 'INFO'   : $objLogger->Info   ( $message, $this->setup );break;
            case 'ERROR'  : $objLogger->Error  ( $message, $this->setup );break;
            case 'WARNING': $objLogger->Warning( $message, $this->setup );break;
            case 'ALERT'  : $objLogger->Alert  ( $message, $this->setup );break;
            case 'DEBUIG' : $objLogger->Debug  ( $message, $this->setup );break;
            case 'NOTICE' : $objLogger->Notice ( $message, $this->setup );break;

        }

        $this->type  = 'INFO';
        $this->prefx = '';

    }

    public function request( $message, $setup = array() ) {
        $this->prefx = str_pad( 'REQUEST' , 12 , ".");
        self::criar( $message, $setup );
    }

    public function result( $message, $setup = array() ) {

        $this->prefx = str_pad( 'RESULT' , 12 , ".");
        self::criar( $message, $setup );
    }

    public function send( $message, $setup = array() ) {
        $this->prefx = str_pad( 'SEND' , 12 , ".");
        self::criar( $message, $setup );
    }

    public function returno( $message, $setup = array() ) {
        $this->prefx = str_pad( 'RETURN' , 12 , ".");
        self::criar( $message, $setup );
    }

    public function set( $message, $setup = array() ) {
        $this->prefx = str_pad( 'SET' , 12 , ".");
        self::criar( $message, $setup );
    }

    private function trataEntrada( $str ) {

        if( is_object( $str ) ) {
            try {
                $str = (string) $str;
            }
            catch (\Exception $e) {
                $str = json_encode( $str );
            }
        }

        return $str;

    }
}