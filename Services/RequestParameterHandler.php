<?php
/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 20/11/16
 * Time: 14:16
 */


namespace EscapeHither\CrudManagerBundle\Services;

use EscapeHither\CrudManagerBundle\Entity\ResourceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\DependencyInjection\ContainerInterface as Container;
use Symfony\Component\HttpFoundation\Request;
use EscapeHither\CrudManagerBundle\Utils\RequestHandlerUtils;


class RequestParameterHandler extends RequestHandlerUtils
{
    protected $name;
    protected $bundleName;
    protected $request;
    protected $requestStack;
    protected $resourceName;
    protected $resourceServiceName;
    protected $resourceConfigName;
    protected $resourceViewName;
    protected $themePath;
    protected $redirectionRoute;
    protected $indexRoute;
    protected $deleteRoute;
    protected $repositoryConfig;
    protected $repositoryClass;
    protected $factoryConfig;
    protected $factoryClass;
    protected $formConfig;
    protected $formClass;
    protected $factoryServiceName;
    protected $format;
    protected $securityConfig;
    protected $routeName;
    protected $actionName;

    function __construct(RequestStack $requestStack, Container $container)
    {
        $this->requestStack = $requestStack;
        $this->container = $container;

    }
    public function build(){
        $this->request = $this->requestStack->getCurrentRequest();
        if($this->request){
            $this->format = $this->request->getRequestFormat();
        }

        $attributes = $this->getAttributes();
        if (!empty($attributes)) {
            $this->resourceName = $attributes['name'];
            $action_list = [
              'indexAction',
              'apiIndexAction',
              'editAction',
              'apiEditAction',
              'showAction',
              'apiShowAction',
              'newAction',
              'apiNewAction',
              'deleteAction',
              'apiDeleteAction',

            ];

            if ($this->resourceName == "redirect") {
                return;
            }
            if (in_array($attributes['action'], $action_list)) {
                // use when call resource configuration parameter.
                $this->resourceConfigName = 'resource-'.$attributes['nameConfig'];

                if ($this->container->hasParameter($this->resourceConfigName)) {

                    $parameters = $this->container->getParameter(
                      $this->resourceConfigName
                    );
                    $this->repositoryClass = $parameters['entity'];
                }

            }

            // use when call resource configuration parameter.
            $this->resourceServiceName = 'resource.'.$attributes['nameConfig'];
            // The name use for generating the view.
            $this->resourceViewName = RequestHandlerUtils::generateResourceViewName(
              $attributes
            );
            // The where is template for the view.
            $this->themePath = $this->generateThemePath($attributes);
            // The bundle name.
            $this->bundleName = $attributes['bundle'];
            // The redirection route.
            $this->redirectionRoute = $this->generateReDirectionRoute(
              $attributes
            );

            // The index root.
            $this->indexRoute = $attributes['nameConfig'].'_index';
            $this->deleteRoute = $this->generateDeleteRoute();
            // Repository configuration.
            $this->repositoryConfig = $attributes['repository'];
            // factory configuration
            $this->factoryConfig = $attributes['factory'];
            $this->formConfig = $attributes['form'];
            $this->securityConfig = $attributes['security'];
            $this->actionName = $attributes['action'];
            $this->routeName = $attributes['_route'];

        }

    }



    /**
     * @return mixed
     */
    public function getActionName() {
        return $this->actionName;
    }

    /**
     * @return mixed
     */
    public function getRouteName() {
        return $this->routeName;
    }



    /**
     * @return string
     */
    public function getIndexRoute()
    {
        return $this->indexRoute;
    }

    /**
     * @return string
     */
    public function getDeleteRoute()
    {
        return $this->deleteRoute;
    }

    /**
     * @return mixed
     */
    public function getFormat() {
        return $this->format;
    }



    /**
     * @return string
     */
    public function getRedirectionRoute()
    {
        return $this->redirectionRoute;
    }

    /**
     * @return string
     */
    public function getRepositoryClass()
    {
        return $this->repositoryClass;
    }

    /**
     * @return string
     */
    public function getThemePath()
    {
        return $this->themePath;
    }

    /**
     * @return string
     */
    public function getResourceViewName()
    {
        return $this->resourceViewName;
    }

    /**
     * @return string
     */
    public function getResourceConfigName()
    {
        return $this->resourceConfigName;
    }

    /**
     * @return mixed
     */
    public function getResourceName()
    {
        return $this->resourceName;
    }

    /**
     * @return mixed
     */
    public function getBundleName()
    {
        return $this->bundleName;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getFactoryServiceName()
    {
        $this->factoryServiceName = $this->resourceServiceName.'.factory';

        return $this->factoryServiceName;
    }


    public function getRepositoryMethod()
    {
        return $this->repositoryConfig['method'];

    }

    public function getRepositoryArguments()
    {

        return $this->repositoryConfig['arguments'];

    }

    public function getFactoryMethod()
    {
        return $this->factoryConfig['method'];

    }

    public function getFactoryArguments()
    {

        if (isset($this->factoryConfig['arguments'])) {
            return $this->factoryConfig['arguments'];
        } else {
            return null;
        }


    }

    /**
     * @param $resource
     * @return array
     */
    public function getRedirectionParameter(ResourceInterface $resource)
    {
        $paramRedirection = $this->request->attributes->get('redirect');
        if (!empty($paramRedirection['parameters'])) {
            foreach ($paramRedirection['parameters'] as $key => $name) {
                $paramRedirection['parameters'][$key] = $this->request->attributes->get(
                  $name
                );
            }
            return $paramRedirection['parameters'];
        }

        elseif ($resource) {
            $route = $this->container->get('router')->getRouteCollection()->get(
              $this->redirectionRoute
            );
            $pathVariables = $route->compile()->getPathVariables();
            $routeParameters = [];
            if (!empty($pathVariables)) {
                 $routeParameters= ['id' => $resource->getId()];
            }
            return $routeParameters;
        }
        return NULL;

    }






}