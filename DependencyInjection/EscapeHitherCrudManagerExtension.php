<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EscapeHitherCrudManagerExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        if (isset($config['resources'])) {
            $resources = $config['resources'];
            $container->setParameter('escape_hither_crud_manager_resources', $config['resources']);
            foreach ($resources as $name => $resource) {
                //TODO THe controller must be we two level
                $container->setParameter('resource-'.$name, $config['resources'][$name]);
                $this->addResourceDefinition($container, $name, $resource);
            }
        }
    }


    /**
     * add Ressource definition
     *
     * @param ContainerBuilder $container The container
     * @param string           $name      The ressource name
     * @param array            $resource  The ressource
     */
    private function addResourceDefinition(ContainerBuilder $container, $name, array $resource)
    {
        // Adding resource as service.
        foreach ($resource as $key => $class) {
            if (!class_exists($class)) {
                throw new \InvalidArgumentException(sprintf('class %s was not found', $class));
            }

            $definition = new Definition($class);
            // add a specific tag
            $definition->addTag('resource');
            $serviceId = sprintf('resource.%s.%s', $name, $key);

            if ('entity' === $key) {
                $container->register($serviceId, $class);

                if (array_key_exists('factory', $resource)) {
                    $definition->setFactory(array(new Reference('resource.'.$name.'.factory'), 'create'));
                }
            } elseif ('factory' === $key) {
                $definition->addArgument(new Reference('doctrine.orm.entity_manager'));
                $container->register($serviceId, $class);
            }

            $container->setDefinition($serviceId, $definition);
        }
    }
}
