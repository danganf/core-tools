<?php

namespace IntercaseTools\MyClass;

class Curl{

    private $timeout           = 15;
    private $connectionTimeout = 10;

    public function __construct () {
        //
    }

    protected function setTimeOut($time)          {$this->timeout           = $time;}
    protected function setconnectionTimeout($time){$this->connectionTimeout = $time;}

    public function send( $url, array $options = [] ){

        $ch = curl_init();
        if (count($options) > 0) {
            if (!empty($options['timeout']))
                $this->timeout = $options['timeout'];

            if (!empty($options['connectionTimeout']))
                $this->connectionTimeout = $options['connectionTimeout'];

            if (!empty($options['json'])) {
                if (!empty($options['header'])) {
                    $options['header'][] = 'Content-Type: application/json';
                    $options['header'][] = 'Content-Length: ' . strlen($options['data']);
                }
            }

            if (!empty($options['header'])) {
                if (is_array($options['header'])) {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, $options['header']);
                } else {
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array($options['header']));
                }
            }

            if (!empty($options['method']))
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $options['method']);

            if (!empty($options['post']) && !empty($options['post']))
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');

            if ( !empty($options['data']) && in_array( array_get( $options, 'method', NULL ), ['POST','PUT','PATCH','DELETE'] ) )
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['data']);
        }

        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectionTimeout);
        //curl_setopt($ch, CURLOPT_REFERER, ( isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : null ) );

        if( array_has( $options, 'returnHeaders' ) ) {
            curl_setopt($ch, CURLOPT_HEADER, 1);
        }

        $result   = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if( array_has( $options, 'returnHeaders' ) ) {
            $info        = curl_getinfo($ch);
            $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $textHeaders = substr($result, 0, $header_size);
            $result      = substr($result, $header_size);

            // buscando dentro dos header retornados o que foi selecionado
            $headersReturn = [];
            $data          = explode("\n",$textHeaders);
            array_shift($data);
            foreach($data as $part){
                $middle = explode(":",$part);
                if( in_array( trim($middle[0]), array_get( $options, 'returnHeaders' ) ) ) {
                    $headersReturn[trim($middle[0])] = trim($middle[1]);
                }
            }

            $arrayResult = [ 'RESULT' => $result, 'HTTP_CODE' => $httpcode, 'HEADER_RETURN' => $headersReturn ];

        } else {
            $arrayResult = [ 'RESULT' => $result, 'HTTP_CODE' => $httpcode ];
        }

        curl_close($ch);

        return $arrayResult;

    }

}