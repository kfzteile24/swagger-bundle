<?php

namespace Draw\SwaggerBundle\Route;

use Symfony\Component\Routing\Route;

interface RouteConditionCheckerInterface
{
    /**
     * @param \Symfony\Component\Routing\Route $route
     *
     * @return bool
     */
    public function evaluateRoute(Route $route);
}
