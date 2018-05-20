<?php

/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Doctrine\Common\Inflector\Inflector;

/**
 * Routing api default loader
 *
 *{@inheritDoc}
 */
class ExtraLoader extends Loader
{
    private $loaded = false;
    private $resourcesConfig;

    /**
     * {@inheritDoc}
     */
    public function __construct($resourcesConfig)
    {
        $this->resourcesConfig = $resourcesConfig;
    }

    /**
     * {@inheritDoc}
     */
    public function load($resource, $type = null)
    {
        if (true === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();
        $actionList = [
            'index' => 'index',
            'update' => 'apiEdit',
            'show' => 'apiShow',
            'new' => 'apiNew',
            'delete' => 'apiDelete',

        ];

        foreach ($this->resourcesConfig as $key => $value) {
            $name = lcfirst(Inflector::pluralize($key));
            $dataConfig = explode('\\', $value['controller']);
            $controllerName = preg_replace('/Controller/', '', $dataConfig[count($dataConfig) - 1]);
            $redirectName = 'api.'.$key.'_index';
    
            foreach ($actionList as $actionKey => $action) {
                $routeName = 'api.'.$key.'_'.$actionKey;

                switch ($actionKey) {
                    case 'index':
                        $path = 'api/'.$name;
                        $method = ['GET', 'OPTIONS'];
                        break;
                    case 'update':
                        // prepare a new route
                        $path = 'api/'.$name.'/{id}';
                        $method = ['PUT', 'PATCH'];
                        break;
                    case 'show':
                        $path = 'api/'.$name.'/{id}';
                        $method = ['GET'];
                        break;
                    case 'new':
                        $path = 'api/'.$name;
                        $method = ['POST'];
                        break;
                    case 'delete':
                        $path = 'api/'.$name.'/{id}';
                        $method = ['DELETE'];
                        break;
                }
    
                $controller = sprintf('%s::%sAction',$value['controller'],$action);
                $defaults = array(
                    '_controller' => $controller,
                    'redirect' => $redirectName,
                );
                /*$requirements = array(
                'parameter' => '\d+',
               );*/
                $route = new Route($path, $defaults);
                $route->setMethods($method);
                // add the new route to the route collection
                $routes->add($routeName, $route);
            }
        }

        $this->loaded = true;

        return $routes;
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, $type = null)
    {
        return 'extra' === $type;
    }
}
