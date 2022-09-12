<?php

namespace Yarm\Phaidra;

use Illuminate\Support\ServiceProvider;

class phaidraServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        //$this->loadViewsFrom(__DIR__.'/views','phaidra');
        //$this->loadMigrationsFrom(__DIR__.'/database/migrations');
        $this->mergeConfigFrom(__DIR__ . '/config/phaidra.php','phaidra');
        $this->publishes([
            //__DIR__ . '/config/bookshelf.php' => config_path('bookshelf.php'),
            //__DIR__.'/views' => resource_path('views/vendor/bookshelf'),
            // Assets
            //__DIR__.'/js' => resource_path('js/vendor'),
        ],'phaidra');

        //after every update
        //run   php artisan vendor:publish [--provider="Yarm\Elasticsearch\ElasticsearchServiceProvider"][--tag="elasticsearch"]  --force
    }

    public function register()
    {

    }

}
