<?php

namespace MarcosHoo\PropelEloquent\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;

class Kernel extends HttpKernel
{
     /**
     * Create a new HTTP kernel instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application  $app
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function __construct(Application $app, Router $router)
    {
        $app['router'] = $app->share(function ($app) {
            if (!class_exists('\Router')) {
                $routerClass = '\MarcosHoo\PropelEloquent\Routing\Router';
            } else {
                $routerClass = '\Router';
            }
            return new $routerClass($app['events'], $app);
        });
        $router = $app['router'];

        parent::__construct($app, $router);
    }
}


