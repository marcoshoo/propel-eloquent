<?php

namespace MarcosHoo\PropelEloquent\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Foundation\Application;
use MarcosHoo\PropelEloquent\RouterClassNotFoundException;
use MarcosHoo\PropelEloquent\Routing\Router as Router;
use Illuminate\Routing\Router as DefaultRouter;

class Kernel extends HttpKernel
{
    /**
     * Create a new HTTP kernel instance.
     *
     * @param  \Illuminate\Contracts\Foundation\Application $app
     * @param  \Illuminate\Routing\Router $router
     * @return void
     */
    public function __construct(Application $app, DefaultRouter $router)
    {
        $app['router'] = $app->share(function ($app) {
            $routerClass = Router::class;
            $rx = new \ReflectionClass($this);
            if ($rx->hasProperty('router')) {
                $prx = $rx->getProperty('router');
                if (!$prx->isPrivate()) {
                    $class = false;
                    if ($prx->isStatic() && class_exists(static::$router)) {
                        $class = static::$router;
                    } elseif (class_exists($this->router)) {
                        $class = $this->router;
                    }
                    if ($class) {
                        $rx = new \ReflectionClass($class);
                        if ($rx->isSubclassOf(Router::class)) {
                            $routerClass = $class;
                        }
                    }
                }
            }
            return new $routerClass($app['events'], $app);
        });
        $router = $app['router'];
        parent::__construct($app, $router);
    }
}
