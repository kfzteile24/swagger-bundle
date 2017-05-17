<?php

namespace Draw\SwaggerBundle\Drawer;

interface SwaggerDrawerInterface
{
    /**
     * @return string
     */
    public function createApiDoc();
}
