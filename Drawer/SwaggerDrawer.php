<?php

namespace Draw\SwaggerBundle\Drawer;

use Draw\Swagger\Swagger;
use Symfony\Component\DependencyInjection\ContainerInterface;

class SwaggerDrawer implements SwaggerDrawerInterface
{
    /**
     * @var \Draw\Swagger\Swagger
     */
    protected $drawSwagger;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $schema;

    /**
     * @param \Draw\Swagger\Swagger $drawSwagger
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param array $schema
     */
    public function __construct(Swagger $drawSwagger, ContainerInterface $container, array $schema)
    {
        $this->drawSwagger = $drawSwagger;
        $this->container = $container;
        $this->schema = $schema;
    }

    /**
     * @return string
     */
    public function createApiDoc()
    {
        $schema = $this->drawSwagger->extract(json_encode($this->schema));
        $schema = $this->drawSwagger->extract($this->container, $schema);

        $jsonSchema = $this->drawSwagger->dump($schema);

        return $jsonSchema;
    }
}
