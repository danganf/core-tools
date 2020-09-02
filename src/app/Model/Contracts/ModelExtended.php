<?php

namespace DanganfTools\Models\Contracts;

use Illuminate\Database\Eloquent\Model;

class ModelExtended extends Model
{
    private $storeID, $companyID;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->storeID   = \Request::get('store_id');
    }

    public function getStoreID(){
        // RESOLVENDO PROBLEMA QDO FAZ INJEÇÃO DE DEPEDENCIA DA MODEL ANTES DO REQUEST ESTA COMPLETO
        if( empty( $this->storeID ) ){$this->storeID = \Request::get('store_id');}
        return $this->storeID;
    }

    public function setFilterStore($alias=null){
        $alias = empty( $alias ) ? '' : $alias.'.';
        return parent::where( $alias.'store_id', \Request::get('store_id') );
    }


}
