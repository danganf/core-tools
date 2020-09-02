<?php

namespace IntercaseTools;

use Illuminate\Support\ServiceProvider as IlluminateSP;

class ServiceProvider extends IlluminateSP
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        #CONFIGs
        $this->mergeConfigFrom(__DIR__ . '/config/default.php', 'moddefault');

        #FACADES
        $app = \Illuminate\Foundation\AliasLoader::getInstance();
        $app->alias('IntercaseValidator', 'IntercaseTools\Facades\IntercaseValidatorFacades');
        $app->alias('ThrowNew'          , 'IntercaseTools\Facades\ThrowNewExceptionFacades');
        $app->alias('LogDebug'          , 'IntercaseTools\Facades\LogDebugFacades');
        $app->alias('CoreConfig'        , 'IntercaseTools\Facades\CoreConfigDataFacades');
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        #CARREGANDO TRADUCOES
        $this->loadTranslationsFrom(__DIR__.'/translations', 'ToolsLang');
    }
}
