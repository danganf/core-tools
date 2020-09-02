<?php

namespace IntercaseTools\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * Class ThrowNewExceptionFacades
 * @package App\Facades
 */
class LogDebugFacades extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'IntercaseTools\MyClass\LogDebug';
    }
}
