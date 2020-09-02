<?php

namespace DanganfTools\MyClass;

use Illuminate\Http\Request;
use Illuminate\Session\Store;
use DanganfTools\Facades\ThrowNewExceptionFacades;

class CoreConfigData
{
    private $data;

    CONST PATH_URL_CDN = 'config/cdn/url';

    private $scope;

    /**
     * CoreConfigData constructor.
     * Resultado da api PDV_URL/store/config
     * @param string $sessionScope
     */
    public function __construct($sessionScope = 'coreConfigData', Store $session)
    {
        try {
            $this->data = $session->get($sessionScope);
            if( empty( $this->data ) ){
                ThrowNewExceptionFacades::Unauthorized(\Lang::get('default.action_error'));
            }
        } catch (\Exception $e) {

            $data = null;
            if( class_exists('\App\Repositories\CoreConfigRepository') ){
                $data = get_instace_repository('CoreConfig')->getConfigs();
            } else if( class_exists('\App\MyClass\FactoryApis') ){
                $factoryApis = \App::make('\App\MyClass\FactoryApis');
                $data        = $factoryApis->get('core_config');
            }

            if( !empty( $data ) ){
                if( !isLocal() ) {
                    $session->put($sessionScope, $data);
                }
                $this->data = $data;
            } else {
                ThrowNewExceptionFacades::Unauthorized(\Lang::get('default.parameters_incorrets'));
            }
        }

    }

    public function __call($method, $args) {
        $return = null;
        if( substr($method,0,3) == 'get' ) {
            $data   = preg_split('/(?=[A-Z])/', $method);
            $method = str_replace(['/get/','get/'], '', strtolower(implode('/', $data)));
            $return = $this->get($method);
                 if( strpos( $method, '/json/' )   !== false ){$return = $this->convertJson( $return );}
            else if( strpos( $method, '/enabled' ) !== false ){$return = (boolean) $return;}
        }
        return $return;
    }

    public function getUrlCdn(){return $this->get( $this::PATH_URL_CDN );}

    public function setScope( $scope )  {$this->scope = $scope;return $this; }
    public function resetScope( $scope ){return $this->setScope(null); }

    private function convertJson($string){return !empty( $string ) ? json_decode( $string, true ) : null;}

    public function get($path){

        $return = null;
        if( is_array( $this->data ) ){
            $result = array_where( $this->data, function($row, $value) use ($path)
            {
                $return = $row['path'] === $path;
                if( !empty( $this->scope ) ){
                    $return = $row['scope'] == $this->scope && $row['path'] === $path;
                }
                return $return;
            });
            $return = !empty( $result ) ? current($result)['value'] : null;
        }

        return $return;
    }

}
