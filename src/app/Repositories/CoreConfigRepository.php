<?php

namespace IntercaseTools\Repositories;

use IntercaseTools\MyClass\Json\Contracts\JsonAbstract;
use IntercaseTools\Repositories\Contracts\RepositoryAbstract;

class CoreConfigRepository extends RepositoryAbstract {

    public function __construct(){
        parent::__construct( __CLASS__ );
        return $this;
    }

    /**
     * @return array|int|mixed|null
     */
    public function getConfigs(){
        $model  = $this->getModel();
        $where  = "(store_id is null or store_id=" . $model->getStoreID() . ')';
        $querie = $model->selectRaw('id, store_id, scope, path, value');
        return $querie->whereRaw( $where )->get()->toArray();
    }

    public function getConfig( $path, $storeID, $scope='system', $isUK = TRUE ){
        $result = $this->getModel()->where('store_id', $storeID)
            ->where('scope', $scope)
            ->where('path', $path)
            ->select('value')
            ->get()->toArray();
        if( !empty( $result ) ){
            $result = current( $result );
            $result = $result['value'];
            $this->convertValue( $path, $result );
        } else {
            $result = null;
        }
        return $result;
    }

    public function createOrUpdate(JsonAbstract $json)
    {
        $model = $this->getModel()->firstOrNew( [
            'store_id' => $json->get('store_id'),
            'scope'    => $json->get('scope'),
            'path'     => $json->get('path'),
        ] );
        $model->value = $json->get('value');
        $model->save();
    }

    public function deleteConfig($path, $scope='system'){
        $model = $this->getModel();
        $model->where('store_id', $model->getStoreID() )
            ->where('scope', $scope)
            ->where('path', $path)
            ->delete();
    }

    private function convertValue($path, &$value){
        if( strpos( $path, '/json/' )        !== false ){$value = !empty( $value ) ? json_decode( $value, true ) : null;}
        else if( strpos( $path, '/enabled' ) !== false ){$value = (boolean) $value;}
    }
}
