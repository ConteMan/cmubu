<?php
/**
 * Date: 2018/10/24
 * Time: 22:15
 */

namespace Boxiaozhi\Cmubu;

use Illuminate\Support\ServiceProvider;

class CmubuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/cmubu.php' => config_path('cmubu.php')
        ], 'config');
    }

    public function register()
    {
        $this->app->singleton('cmubu', function(){
            return new Cmubu;
        });
    }
}