<?php

namespace Draw\SwaggerBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DrawSwaggerExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Loads a specific configuration.
     *
     * @param array $config An array of configuration values
     * @param ContainerBuilder $container A ContainerBuilder instance
     *
     * @throws \InvalidArgumentException When provided tag is not defined in this extension
     *
     * @api
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('draw_swagger.schema', $config['schema']);
        $container->setParameter('draw_swagger.api_path', $config['apiPath']);

        $fileLocator = new FileLocator(__DIR__ . '/../Resources/config');
        $loader = new YamlFileLoader($container, $fileLocator);
        $loader->load('swagger.yml');

        $definition = $container->getDefinition("draw.swagger.extrator.type_schema_extractor");

        foreach ($config['definitionAliases'] as $alias) {
            $definition->addMethodCall(
                'registerDefinitionAlias',
                [$alias['class'], $alias['alias']]
            );
        }
    }

    /**
     * Allow an extension to prepend the extension configurations.
     *
     * @param ContainerBuilder $container
     */
    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig(
            'draw_swagger',
            ['schema' => ['info' => [], 'tags' =>[]]]
        );
    }
}
