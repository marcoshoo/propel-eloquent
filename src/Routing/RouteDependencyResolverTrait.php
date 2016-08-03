<?php

namespace MarcosHoo\PropelEloquent\Routing;

use ReflectionMethod;
use ReflectionParameter;
use ReflectionFunctionAbstract;
use MarcosHoo\PropelEloquent\Model\Contracts\RequestObjectContract;
use Illuminate\Http\Request;

trait RouteDependencyResolverTrait
{
    /**
     * Resolve the object method's type-hinted dependencies.
     *
     * @param  array  $parameters
     * @param  object  $instance
     * @param  string  $method
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function resolveClassMethodDependenciesWithRequest(array $parameters, $instance, $method, $request)
    {
        if (! method_exists($instance, $method)) {
            return $parameters;
        }

        return $this->resolveMethodDependenciesWithRequest(
            $parameters, new ReflectionMethod($instance, $method), $request
        );
    }

    /**
     * Resolve the given method's type-hinted dependencies.
     *
     * @param  array  $parameters
     * @param  \ReflectionFunctionAbstract  $reflector
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function resolveMethodDependenciesWithRequest(array $parameters, ReflectionFunctionAbstract $reflector, $request)
    {
        $originalParameters = $parameters;
        $request = clone $request;

        foreach ($reflector->getParameters() as $key => $parameter) {
            $instance = $this->transformDependencyWithRequest(
                $parameter, $parameters, $originalParameters, $request
            );

            if (! is_null($instance)) {
                $this->spliceIntoParameters($parameters, $key, $instance);
            }
        }

        return $parameters;
    }

    /**
     * Attempt to transform the given parameter into a class instance.
     *
     * @param  \ReflectionParameter  $parameter
     * @param  array  $parameters
     * @param  array  $originalParameters
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    protected function transformDependencyWithRequest(ReflectionParameter $parameter, $parameters, $originalParameters, $request)
    {
        $class = $parameter->getClass();

        if (!property_exists($this, '_request')) {
            $this->_request = $request;
        }

        // If the parameter has a type-hinted class, we will check to see if it is already in
        // the list of parameters. If it is we will just skip it as it is probably a model
        // binding and we do not want to mess with those; otherwise, we resolve it here.
        if ($class && ! $this->alreadyInParameters($class->name, $parameters)) {

            $instance = $this->container->make($class->name);

            $r = new \ReflectionClass($instance);

            if ($r->isSubclassOf(Request::class)) {
                $this->_request = $instance;
            }

            if ($instance instanceof RequestObjectContract) {
                $instance->fromRequest($this->_request);
            }

            return $instance;
        }
    }
}
