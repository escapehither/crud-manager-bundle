<?php

namespace StarterKit\CrudBundle\DependencyInjection;

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
class StarterKitCrudExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        if (isset($config['resources'])) {
            $resources = $config['resources'];
            foreach ($resources as $name => $resource) {
                $container->setParameter('resource-'.$name, $config['resources'][$name]);
                $this->addResourceDefinition($container, $name, $resource);
            }
        }
    }
    private function addResourceDefinition(ContainerBuilder $container, $name, array $resource)
    {
        // Adding resource as service.
        foreach ($resource as $key => $value) {
            // You need to add a if to see if the file exist the process.
            // Else throw new \InvalidArgumentException('not found');
            $class = $value;
            $definition = new Definition($class);
            //$definition->addArgument(new Reference('doctrine'));
            // add a specific tag
            $definition->addTag('resource');
            $serviceId = 'resource.' . $name.'.'.$key;
            //var_dump($key);
            if ($key == "controller") {
            }
            elseif ($key == "entity") {
                //call on a static method.
                //$definition->setFactory(array($class, 'create'));
                $container->register($serviceId, $class);
                //TODO: CHECK if the factory is set.
                if(array_key_exists('factory',$resource)){
                    $definition->setFactory(array(new Reference('resource.' . $name.'.factory'), 'create'));
                }

            }
            elseif ($key == "form") {
            }
            elseif ($key == "repository") {
                //$definition->addArgument(new Reference('doctrine.orm.entity_manager'));
                //$definition->
                //$container->register($serviceId, $class);
            }
            elseif ($key == "factory") {
                $definition->addArgument(new Reference('doctrine.orm.entity_manager'));
                $container->register($serviceId, $class);
            }
            $container->setDefinition($serviceId, $definition);

        }

    }
}
