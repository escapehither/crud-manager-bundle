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


class RequestParameterHandler
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

            } else {
                // Repository.
                //$this->repositoryClass = $attributes['rootBundle'] . ':' . $attributes['resource'] ;
            }

            // use when call resource configuration parameter.
            $this->resourceServiceName = 'resource.'.$attributes['nameConfig'];
            // The name use for generating the view.
            $this->resourceViewName = $this->generateResourceViewName(
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
        }

    }

    /**
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
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

    /**
     * @return mixed
     */
    public function getAttributes()
    {
        $attributes = [];
        if ($this->request) {
            $controllerLink = $this->request->attributes->get('_controller');
            $paramController = explode("\\", $controllerLink);
            $controllerAction = $paramController[count($paramController) - 1];
            $controllerActionTab = explode("::", $controllerAction);
            $attributes['rootClass'] = $this->getRootClass($paramController);
            $attributes['rootBundle'] = $this->getRootBundle($paramController);
            if (!empty($paramController)) {
                $attributes['bundle'] = $paramController[0];
            } else {
                $attributes['bundle'] = null;
            }

            $controller = $controllerActionTab[0];
            $pattern = '/Controller/';
            $replacement = '';
            $attributes['resource'] = preg_replace(
              $pattern,
              $replacement,
              $controller
            );
            // The resource name available in template.
            $attributes['name'] = lcfirst($attributes['resource']);
            // The resource name for the template folder.
            $attributes['template'] = strtolower($attributes['resource']);
            // The resource name for getting config parameters.
            $attributes['nameConfig'] = $this->from_camel_case(
              $attributes['resource']
            );
            if (!empty($controllerActionTab[1])) {
                $attributes['action'] = $controllerActionTab[1];
            } else {
                $attributes['action'] = null;
            }
            $attributes['repository'] = $this->getRepositoryConfig();
            $attributes['factory'] = $this->getFactoryConfig();
            $attributes['form'] = $this->getFormConfig();
            $attributes['security'] = $this->getSecurityConfig();
        }


        return $attributes;
    }

    /**
     * @return mixed
     */
    public function getRepositoryConfig()
    {
        $repositoryConfig = $this->request->attributes->get('repository');
        if (isset($repositoryConfig['arguments'])) {
            foreach ($repositoryConfig['arguments'] as $key => $value) {
                $repositoryConfig['arguments'][$key] = $this->request->query->get(
                  $value
                );
            }

        } else {
            $repositoryConfig['arguments'] = null;
        }
        if (!isset($repositoryConfig['method'])) {
            $repositoryConfig['method'] = null;

        }

        return $repositoryConfig;


    }

    /**
     * @return mixed
     */
    public function getFactoryConfig()
    {
        $factoryConfig = $this->request->attributes->get('factory');
        if (isset($factoryConfig['arguments'])) {
            foreach ($factoryConfig['arguments'] as $key => $value) {
                $factoryConfig['arguments'][$key] = $this->request->attributes->get(
                  $value
                );
            }

        }

        return $factoryConfig;
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
     *  Get The form Configuration.
     * @return mixed
     */
    public function getFormConfig()
    {
        $FormConfig = $this->request->attributes->get('form');

        return $FormConfig;

    }
    /**
     *  Get The security Configuration.
     * @return mixed
     */
    public function getSecurityConfig()
    {
        $securityConfig = $this->request->attributes->get('security');

        return $securityConfig;

    }

    /**
     *  Generate the resource name uses for the template.
     * @param $attributes
     *   The attributes from the request.
     * @return string
     */
    public function generateResourceViewName($attributes)
    {

        if ($attributes['action'] == "indexAction") {
            $name = $attributes['name'].'s';
        } else {
            $name = $attributes['name'];
        }

        return $name;
    }

    public function generateRepositoryConfig(array $attributes, $request)
    {

    }

    /**
     *  Generate the theme path.
     * @param array $attributes
     *  The request attributes
     * @return null|string
     */
    public function generateThemePath(array $attributes)
    {
        // Check if the template is set in the routing attributes.
        $paramTemplate = $this->request->attributes->get('template');
        $path = null;
        $this->request->query->get('template');
        if (isset($this->request->query) && $this->request->query->get(
            'template'
          )
        ) {
            $path = $this->request->query->get('template');
        } elseif (isset($paramTemplate)) {
            return $paramTemplate;
        } else {
            switch ($attributes['action']) {
                case "indexAction":
                    $path = $attributes['template'].'/index.html.twig';
                    break;
                case "newAction":
                    $path = $attributes['template'].'/new.html.twig';
                    break;
                case "showAction":
                    $path = $attributes['template'].'/show.html.twig';
                    break;
                case "editAction":
                    $path = $attributes['template'].'/edit.html.twig';
                    break;
            }

        }

        return $path;

    }

    /**
     *  Get the delete route from the routing parameter.
     * @return string
     */
    public function generateDeleteRoute()
    {

        $paramDeleteRoute = $this->request->attributes->get('delete_route');
        if (isset($paramDeleteRoute)) {
            return $paramDeleteRoute;
        }
        $route = false;

        return $route;

    }

    public function generateReDirectionRoute(array $attributes)
    {

        $paramRedirectionRoute = $this->request->attributes->get('redirect');
        if (isset($paramRedirectionRoute)) {
            if (!empty($paramRedirectionRoute['route'])) {
                return $paramRedirectionRoute['route'];
            } else {
                return $paramRedirectionRoute;
            }

        }
        $route = null;
        switch ($attributes['action']) {
            case "indexAction":
                $route = $attributes['nameConfig'].'_index';
                break;
            case "newAction":
                $route = $attributes['nameConfig'].'_show';
                break;
            case "editAction":
                $route = $attributes['nameConfig'].'_show';
                break;
            case "deleteAction":
                $route = $attributes['nameConfig'].'_index';
                break;
        }

        return $route;

    }

    /**
     * @param $resource
     * @return array
     */
    public function getRedirectionParameter(ResourceInterface $resource)
    {
        $paramRedirection = $this->request->attributes->get('redirect');
        if (isset($paramRedirection['parameters']) && !empty($paramRedirection['parameters'])) {
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

    /**
     * @return mixed
     */
    public function getRouteParameter()
    {
        return $this->request->attributes->get('_route_params');

    }

    /**
     * @param $paramController
     * @return string
     */
    public function getRootBundle($paramController)
    {
        $rootBundle = '';
        for ($i = 0; $i <= count($paramController) - 3; $i++) {
            $rootBundle .= $paramController[$i];
        }

        return $rootBundle;
    }

    /**
     * @param $paramController
     * @return string
     */
    public function getRootClass($paramController)
    {
        $rootClass = '';
        for ($i = 0; $i <= count($paramController) - 3; $i++) {
            $rootClass .= $paramController[$i].'\\';
        }
        $rootClass = substr($rootClass, 0, -1);

        return $rootClass;
    }


    /**
     * @return string
     */

    public function getResourceClass()
    {
        $attributes = $this->getAttributes();
        $resourceClass = $attributes['rootClass'].'\Entity\\'.$attributes['resource'];

        return $resourceClass;
    }

    /**
     * Transform a string from camel_case to underscore.
     * @param $input
     * @return string
     *  A string lowercase with underscore pattern.
     */

    public function from_camel_case($input)
    {
        preg_match_all(
          '!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!',
          $input,
          $matches
        );
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = $match == strtoupper($match) ? strtolower(
              $match
            ) : lcfirst($match);
        }

        return implode('_', $ret);
    }

}