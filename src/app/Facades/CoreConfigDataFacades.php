<?php

namespace DanganfTools\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ThrowNewExceptionFacades
 * @package App\Facades
 */
class CoreConfigDataFacades extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'DanganfTools\MyClass\CoreConfigData';
    }
}
