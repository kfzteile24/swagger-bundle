<?php

namespace Draw\SwaggerBundle\Route;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class RouteConditionChecker implements RouteConditionCheckerInterface
{
    /**
     * @var \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    protected $expressionLanguage;

    /**
     * @var \Symfony\Component\HttpFoundation\Request
     */
    protected $request;

    /**
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param \Symfony\Component\Routing\Route $route
     *
     * @return bool
     */
    public function evaluateRoute(Route $route)
    {
        $condition = $route->getCondition();
        $expressionLanguage = $this->getExpressionLanguage();
        $values = [
            'context' => new RequestContext(),
            'request' => $this->request
        ];

        if ($condition && !$expressionLanguage->evaluate($condition, $values)) {
            return false;
        }

        return true;
    }

    /**
     * @return \Symfony\Component\ExpressionLanguage\ExpressionLanguage
     */
    protected function getExpressionLanguage()
    {
        if (null === $this->expressionLanguage) {
            if (!class_exists('Symfony\Component\ExpressionLanguage\ExpressionLanguage')) {
                throw new \RuntimeException(
                    'Unable to use expressions as the Symfony ExpressionLanguage component is not installed.'
                );
            }

            $this->expressionLanguage = new ExpressionLanguage();
        }

        return $this->expressionLanguage;
    }
}
