<?php

namespace MarcosHoo\PropelEloquent\Routing;

use Illuminate\Database\Eloquent\Model;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
use \Illuminate\Routing\Router as IlluminateRouter;

/**
 *
 * @author marcos
 *
 */
class Router extends IlluminateRouter
{
    /**
     * Create a new Route object.
     *
     * @param  array|string  $methods
     * @param  string  $uri
     * @param  mixed  $action
     * @return \Illuminate\Routing\Route
     */
    protected function newRoute($methods, $uri, $action)
    {
        return (new Route($methods, $uri, $action))
            ->setRouter($this)
            ->setContainer($this->container);
    }

    /**
     * Substitute the implicit Eloquent\Propel model bindings for the route.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @return void
     */
    protected function substituteImplicitBindings($route)
    {
        $parameters = $route->parameters();

        foreach ($route->signatureParameters(ActiveRecordInterface::class) as $parameter) {
            $class = $parameter->getClass();

            if (array_key_exists($parameter->name, $parameters) &&
                ! $route->getParameter($parameter->name) instanceof ActiveRecordInterface) {

                $query = '\\' . $class->getName() . 'Query';
                $query = new $query;
                $model = $query->filterByPrimaryKey($parameters[$parameter->name])->requireOne();

                $route->setParameter($parameter->name, $model);
            }
        }

        foreach ($route->signatureParameters(Model::class) as $parameter) {
            $class = $parameter->getClass();

            if (array_key_exists($parameter->name, $parameters) &&
                ! $route->getParameter($parameter->name) instanceof Model) {
                $method = $parameter->isDefaultValueAvailable() ? 'first' : 'firstOrFail';

                $model = $class->newInstance();

                $route->setParameter(
                    $parameter->name, $model->where(
                        $model->getRouteKeyName(), $parameters[$parameter->name]
                    )->{$method}()
                );
            }
        }
    }
}
