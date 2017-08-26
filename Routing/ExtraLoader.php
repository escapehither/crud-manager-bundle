<?php

/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 09/08/17
 * Time: 19:49
 */
namespace EscapeHither\CrudManagerBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class ExtraLoader extends Loader {
    private $loaded = FALSE;
    private $resourcesConfig;

    public function __construct($resourcesConfig) {
        $this->resourcesConfig = $resourcesConfig;
    }

    public function load($resource, $type = NULL) {
        if (TRUE === $this->loaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $routes = new RouteCollection();
        $action_list = [
            'index' => 'index',
            'update' => 'apiEdit',
            'show' => 'apiShow',
            'new' => 'apiNew',
            'delete' => 'apiDelete',

        ];
        foreach ($this->resourcesConfig as $key => $value) {

            $name = $key . 's';
            $data_config = explode('\\', $value['controller']);
            $controllerName = preg_replace('/Controller/', '', $data_config[count($data_config) - 1]);
            $redirectName = 'api.' . $key . '_index';
            foreach ($action_list as $key_action => $action) {
                $routeName = 'api.' . $key . '_' . $key_action;
                switch ($key_action) {
                    case 'index':
                        $path = 'api/' . $name;
                        $method = ['GET'];
                        break;
                    case 'update':
                        // prepare a new route
                        $path = 'api/' . $name . '/{id}';
                        $method = ['PUT', 'PATCH'];
                        break;
                    case 'show':
                        $path = 'api/' . $name . '/{id}';
                        $method = ['GET'];
                        break;
                    case 'new':
                        $path = 'api/' . $name;
                        $method = ['POST'];
                        break;
                    case 'delete':
                        $path = 'api/' . $name . '/{id}';
                        $method = ['DELETE'];
                        break;
                }
                $controller = $data_config[0] . ':' . $controllerName . ':' . $action;
                $defaults = array(
                    '_controller' => $controller,
                    'redirect'=> $redirectName
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

        $this->loaded = TRUE;
        return $routes;
    }

    public function supports($resource, $type = NULL) {
        return 'extra' === $type;
    }
}