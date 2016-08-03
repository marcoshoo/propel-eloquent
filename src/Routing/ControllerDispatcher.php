<?php

namespace MarcosHoo\PropelEloquent\Routing;

use Illuminate\Http\Request;
use Illuminate\Container\Container;
use Illuminate\Routing\Controller;
use Illuminate\Routing\Pipeline;
use Illuminate\Routing\ControllerDispatcher as LaravelControllerDispatcher;

class ControllerDispatcher extends LaravelControllerDispatcher
{
    use RouteDependencyResolverTrait;

    /**
     * Call the given controller instance method.
     *
     * @param  \Illuminate\Routing\Controller  $instance
     * @param  \Illuminate\Routing\Route  $route
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $method
     * @return mixed
     */
    protected function callWithinStack($instance, $route, $request, $method)
    {
        $shouldSkipMiddleware = $this->container->bound('middleware.disable') &&
            $this->container->make('middleware.disable') === true;

        $middleware = $shouldSkipMiddleware ? [] : $this->getMiddleware($instance, $method);

        // Here we will make a stack onion instance to execute this request in, which gives
        // us the ability to define middlewares on controllers. We will return the given
        // response back out so that "after" filters can be run after the middlewares.
        return (new Pipeline($this->container))
            ->send($request)
            ->through($middleware)
            ->then(function ($request) use ($instance, $route, $method) {
                return $this->router->prepareResponse(
                    $request, $this->callWithRequest($instance, $route, $method, $request)
                );
            });
    }

    /**
     * Call the given controller instance method.
     *
     * @param  \Illuminate\Routing\Controller  $instance
     * @param  \Illuminate\Routing\Route  $route
     * @param  string  $method
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function callWithRequest($instance, $route, $method, $request)
    {
        $parameters = $this->resolveClassMethodDependenciesWithRequest(
            $route->parametersWithoutNulls(), $instance, $method, $request
        );

        return $instance->callAction($method, $parameters);
    }
}
