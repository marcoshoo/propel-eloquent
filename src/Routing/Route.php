<?php

namespace MarcosHoo\PropelEloquent\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Route as IlluminateRoute;

class Route extends IlluminateRoute
{
    /**
     * Run the route action and return the response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function runController(Request $request)
    {
        list($class, $method) = explode('@', $this->action['uses']);

        return (new ControllerDispatcher($this->router, $this->container))
            ->dispatch($this, $request, $class, $method);
    }
}
