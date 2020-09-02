<?php

namespace IntercaseTools\Repositories\Contracts;

use IntercaseTools\Exceptions\ApiException;

abstract class RepositoryAbstract implements RepositoryInterface
{
    private $model;
    private $selectFields=[];
    private $uniqueFields=false;
    private $whereFields=[];
    private $relateds=[];
    private $with=[];
    private $msgError=null;
    private $order=null;
    private $resultInArray=false;
    private $limit=null;

    function __construct( $modelBind, $model=null ){
        if( !is_object( $modelBind ) ) {
            $modelName = str_replace('Repository', '', last( explode('\\', $modelBind ) ) );
            $modelBind = $this->factory( $modelName );
        }
        if ( !$model instanceof $modelBind ) {
            $model = new $modelBind();
        }
        if ( is_object( $model ) )
            $this->model = $model;
    }
    public function bindModel( $instanceModel ){
        if ( $instanceModel instanceof $this->model ) {
            $this->model = $instanceModel;
        }
    }
    public function propEmpty($property){
        return empty($this->get( $property ));
    }
    public function set( $campo, $valor ){
        $this->model->$campo = $valor;
        return $this;
    }
    public function setByArray( $array ){
        foreach ( $array as $key => $value ) {
            $this->set( $key, $value );
        }
        return $this;
    }
    public function setReturnArray( $boolean=true ){
        $this->resultInArray = $boolean;
        return $this;
    }
    public function setLimit( Int $value ){
        $this->limit = (int)$value;
        return $this;
    }
    public function setFieldsUnique($values){
        $this->setFields( $values );
        $this->uniqueFields = TRUE;
        return $this;
    }
    public function setFields($values){
        if( !$this->uniqueFields && !empty( $values ) ) {
            $values = !is_array($values) ? explode(',', $values) : $values;
            foreach ($values AS $row) {
                $row = trim( $row );
                if( !in_array( $row, $this->selectFields ) ) {
                    $this->selectFields[] = $row;
                }
            }
        }
        return $this;
    }
    public function setWhere( $values, $operator = '' ){
        if( !empty( $values ) ) {

            if( empty( $operator ) && !empty( $this->whereFields ) ){$operator = 'and';}

            $values = !is_array($values) ? explode(',', $values) : $values;
            foreach ($values AS $row) {
                $operator            = empty( $operator ) ? '' : trim($operator);
                $this->whereFields[] = trim("$operator ($row)");
            }
        }
        return $this;
    }
    public function setRelated($values){
        if( !empty( $values ) ) {
            $values = !is_array($values) ? explode(',', $values) : $values;
            foreach ($values AS $row) {
                $this->relateds[] = trim( $row );
            }
        }
        return $this;
    }

    public function setRelatedFields( $relatedName, $arrayFilter=null ){
        $model       = $this->getModel();
        $arrayFilter = !empty( $arrayFilter ) ? $arrayFilter : '*';

        $model->with([ $relatedName => function($query) use ($arrayFilter) {
            $query->select( $arrayFilter );
        }]);

        $this->bindModel( $model );

        return $this;
    }
    public function setMultiFilter( $field, $value, $operator='or' ){
        $values   = !is_array($value) ? [$value] : $value;
        $where    = '';
        foreach ( $values as $row ){$where .= "($field='$row') $operator ";}
        $this->setFilter( rtrim( $where, "$operator " ) );
        return $this;
    }
    public function setOrderBy($orderRaw){ $this->order = $orderRaw;return $this; }
    public function setMsgError($msg){$this->msgError = $msg; return $this;}
    public function get( $campo, $returnDefault=FALSE ){
        $valor = ( isset ( $this->model->$campo ) ? $this->model->$campo : $returnDefault );
        return $valor;
    }
    public function has($campo){
        return ( !empty( $this->get($campo) ) ? true : false );
    }
    public function save(){
        return $this->model->save();
    }
    public function update( $lista = [ ] ){
        return $this->model->update( $lista );
    }
    public function delete( $key, $uk='id' ){
        $return     = FALSE;
        $collection = $this->getModel()->where( $uk, $key )->first();
        if( !empty( $collection ) ){
            $return = $collection->delete();
        }
        return $return;
    }
    public function getLastID( $key = 'id' ){
        return ( isset ( $this->model->{$key} ) ? $this->model->{$key} : FALSE );
    }

    /**
     * @return object
     */
    public function getModel(){
        return $this->model;
    }
    public function getRelated($reset=true){
        $rel = $this->relateds;
        if( $reset === true ){$this->relateds=[];}
        return $rel;
    }

    public function search(){
        return $this->processWhereAndFields()->get()->toArray();
    }

    public function first(){
        $result = $this->processWhereAndFields()->first();
        return $result ? ( !$this->resultInArray ? $result : $result->toArray() ) : ( !$this->resultInArray ? null : [] );
    }

    /**
     * @return instance Model | null
     */
    private function processWhereAndFields(){
        $model = $this->model;
        if( $this->whereFields ){
            $model = $model->whereRaw( implode(' ', $this->whereFields) );
        }
        if( !empty( $this->getSelectFields() ) ){
            $fieldsRaw          = implode(', ', $this->getSelectFields(true) );
            $model              = $model->selectRaw($fieldsRaw);
            $this->uniqueFields = FALSE;
        }
        if( $this->with ){
            foreach( $this->with as $with ){
                $fields    = is_array( $with['fields'] ) ? $with['fields'] : explode(',', str_replace(', ', ',', $with['fields']));
                $filterRaw = empty( $with['filterRaw'] ) ? null : $with['filterRaw'];
                $model->with([ $with['name'] => function($query) use ($fields,$filterRaw) {
                    if( !empty( $fields ) && is_array( $fields ) ) {
                        $query->select($fields);
                    }
                    if( !empty( $filterRaw ) ) {
                        $query->whereRaw( $filterRaw );
                    }
                }]);
            }
            $this->with = [];
        }
        if( $this->limit ){
            $model->limit( $this->limit );
            $this->limit = null;
        }
        return $model;
    }

    public function setFilterStore($alias=null){
        $alias = empty( $alias ) ? '' : $alias.'.';
        $this->model = $this->model->where( $alias.'store_id', \Request::get('store_id') );
        return $this;
    }
    public function find( $valor, $fieldsRaw=null ){
        $model  = $this->model;

        if( empty( $fieldsRaw ) && !empty( $this->getSelectFields() ) ){
            $fieldsRaw          = implode(',', $this->getSelectFields(true) );
            $this->uniqueFields = FALSE;
        }

        if( !empty( $fieldsRaw ) ){
            $model = $model->selectRaw($fieldsRaw);
        }
        $result = $model->find( $valor );
        if( !is_null($result ) ) {
            $this->model = $result;
        } else {
            $this->setMsgError(\Lang::get('default.registers_not_found'));
        }
        return $this;
    }
    public function increment( $campo ){
        return $this->model->increment( $campo );
    }
    public function findBy( $campoUnico, $documento ) {
        $buildQuery = $this->getModel()->where($campoUnico,$documento);
        $this->processSelectFields( $buildQuery );
        $result     = $buildQuery->first();
        if( !is_null($result ) ) {
            $this->model = $result;
        } else {
            $this->setMsgError(\Lang::get('default.registers_not_found'));
        }
        return $this;
    }

    /**
     * @param $array
     * @return $this
     */
    public function findByArray( $array ) {
        $buildQuery = $this->getModel();
        foreach ( $array as $field => $value ){
            $buildQuery->where( $field, $value);
        }

        $this->processSelectFields( $buildQuery );
        $result     = $buildQuery->first();
        if( !is_null($result ) ) {
            $this->model = $result;
        }
        return $this;
    }
    public function findAll( $campo, $documento ) {
        $buildQuery = $this->getModel()->where($campo,$documento);
        $this->processSelectFields( $buildQuery );
        $return     = $buildQuery->get()->toArray();
        return $return;
    }
    public function findIn( $array, $key='id' ) {
        $buildQuery = $this->getModel()->whereIn( $key, $array );
        $this->processSelectFields( $buildQuery );
        $return = $buildQuery->get()->toArray();
        return $return;
    }
    public function firstOrNew( $value, $field='id' ){
        $this->bindModel( $this->getModel()->firstOrNew( [ $field => $value ] ) );
        return $this;
    }
    protected function processSelectFields(&$buildQuery, $selectRaw='*'){
        $selectRaw = $this->processAlias( $selectRaw );
        if( is_array( $selectRaw ) ) {
            $buildQuery->select($selectRaw);
        } else {
            $buildQuery->selectRaw($selectRaw);
        }
        $this->selectFields = [];
    }
    private function processAlias( $selectRaw='*' ){
        $selectRaw = empty( $this->selectFields ) ? $selectRaw : $this->selectFields;
        //return $selectRaw;
        if( $selectRaw !== '*' ){
            if( !is_array( $selectRaw ) ){
                $selectRaw = explode(',', $selectRaw);
            }
            foreach ( $selectRaw as $key => $value ){
                $selectRaw[ $key ] = trim( $value );
            }
        }
        return $selectRaw;
    }
    public function all(){
        return $this->getModel()->all();
    }
    public function toArray(){
        return $this->getModel()->toArray();
    }
    public function fails(){
        try {
            $return = (empty($this->getModel()->getKey()) ? TRUE : FALSE);
        } catch (\Exception $e){
            $return = true;
        }
        return $return;
    }
    private function factory( $modelBind ){

        $factoryFind = [
            app_path('Models/')                                            => 'App\Models\\',
            //base_path('vendor/intercase/magali-apicenter/src/app/Models/') => 'MagaliApi\Models\\',
        ];

        $instance = null;
        foreach ( $factoryFind as $path => $namespace )
        if ( file_exists($path . $modelBind . '.php' ) ) {
            $instance = \App::make($namespace . $modelBind);
        }

        if( empty( $instance ) ){ throw new ApiException( 'Instancia ' . $modelBind . ' não encontrada' ); }

        return $instance;

    }

    public function startLog()   {\DB::enableQueryLog();}
    public function showLog()    {return \DB::getQueryLog();}
    public function getMsgError(){return $this->msgError;}

    public function getSelectFields($reset=false,$returnDefautl=null){
        $return = $this->selectFields;
        if($reset === true) $this->selectFields = null;
        return !empty( $return ) ? $return : $returnDefautl;
    }
    public function getWhere($reset=false){
        $return = $this->whereFields;
        if($reset === true) $this->resetWhere();
        return $return;
    }
    public function resetWhere(){
        $this->whereFields = [];
        return $this;
    }
    public function getOrderBy($reset=false){
        $order = $this->order;
        if($reset === true) $this->order = null;
        return $order;
    }

    /**
     * @param string $relatedName
     * @param array $arrFields
     * @return $this
     */
    public function setWith( $relatedName, $arrFields=[], $filterRaw=null ){
        $this->with[] = [ 'name' => $relatedName, 'fields' => $arrFields, 'filterRaw' => $filterRaw ];
        return $this;
    }

    public function setRequestFilters( $where, $arrayFilter, $prefx='' ){

        if( !empty( trim( array_get( $arrayFilter, 'code', '' ) ) ) )         {$this->setFilter($where, "code='".$arrayFilter['code']."'");}
        if( !empty( trim( array_get( $arrayFilter, 'document', '' ) ) ) )     {$this->setFilter($where, "document='".$arrayFilter['document']."'");}
        if( !empty( trim( array_get( $arrayFilter, 'phone', '' ) ) ) )        {$this->setFilter($where, "phone='".$arrayFilter['phone']."'");}
        if( !empty( trim( array_get( $arrayFilter, 'cellphone', '' ) ) ) )    {$this->setFilter($where, "cellphone='".$arrayFilter['cellphone']."'");}
        if( !empty( trim( array_get( $arrayFilter, 'id', '' ) ) ) )           {$this->setFilter($where, "id='".$arrayFilter['id']."'");}
        if( !empty( trim( array_get( $arrayFilter, 'type', '' ) ) ) )         {$this->setFilter($where, "type='".strtoupper( $arrayFilter['type'] )."'");}
        if( !empty( trim( array_get( $arrayFilter, 'sku', '' ) ) ) )          {$this->setFilter($where, "sku='".strtoupper( $arrayFilter['sku'] )."'");}

        if( array_has( $arrayFilter, 'ids', '' ) ){
            $this->setFilter($where, "id IN (".implode( ',',$arrayFilter['ids'] ).")");
        }

        #feito dessa forma, pq o zero deve ser considerado
        $stringFilter  = 'status';
        foreach ( explode(',', $stringFilter ) as $row ){
            $filterValue = convert_sn_bool( array_get( $arrayFilter, $row, '' ) );
            if( !is_null( $filterValue ) ) {
                $this->setFilter($where, $row . '=' . $filterValue);
            }
        }

        return trim( $where );

    }

    public function setFilter(&$where, $value=null,$operator=''){
        $operator = empty( $operator ) ? ( empty( trim( $where ) ) ? '' : 'and' ) : $operator;
        $where   .= rtrim(" $operator ( $value )", ' ');
        //dd( $where, $value );
        return $this;
    }

    public function setStatusTrue( $operator=null, $alias=null, $directModel=false ){

        $operator = !empty( $operator ) ? $operator : '';

        $alias = empty( $alias ) ? '' : $alias.'.';
        if( !$directModel )
            $this->setWhere($alias . 'status=1', $operator);
        else
            $this->model = $this->model->where( $alias.'status', 1 );

        return $this;
    }
    public function setStatusFalse( $operator=null, $alias=null, $directModel=false ){
        $operator = !empty( $operator ) ? $operator : '';

        $alias = empty( $alias ) ? '' : $alias.'.';
        if( !$directModel )
            $this->setWhere($alias . 'status=0', $operator);
        else
            $this->model = $this->model->where( $alias.'status', 0 );

        return $this;
    }

    private function setStatus($status, $operator=''){
        $operator = empty( $operator ) ? ( empty( trim( $this->filter ) ) ? '' : 'and' ) : $operator;
        $this->setFilter("status=$status",$operator);
    }

}
