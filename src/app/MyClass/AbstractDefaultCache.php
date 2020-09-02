<?php

namespace DanganfTools\MyClass;

use Illuminate\Cache\Repository;

class AbstractDefaultCache
{
    private $cache, $store, $prefix;
    private $time = 30;#minute

    public function __construct (Repository $cache)
    {
        $this->cache  = $cache;
        $this->store  = getenv('CACHE_DRIVER');
        $this->prefix = 'defaultCache_';
    }

    public function has($key){
        return $this->cache->has( $this->setKey( $key ) );
    }

    public function get($key){
        return $this->cache->get( $this->setKey( $key ) );
    }

    public function forget($key){
        return $this->cache->forget( $this->setKey( $key ) );
    }

    public function create($key,$value,$time=null){
        if( !empty( $value ) ) {
            $time = empty($time) ? $this->time : $time;
            $this->cache->add($this->setKey($key), $value, $time);
        }
    }

    public function setTime($time){
        $this->time = $time;
        return $this;
    }

    private function setKey($key){
        return $this->prefix . $key;
    }

    public function setPrefix($key){
        $this->prefix = $key;
        return $this;
    }
}
