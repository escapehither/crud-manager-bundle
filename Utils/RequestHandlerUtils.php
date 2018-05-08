<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Utils;

/**
 * Request Handler Utils
 *
 * @author <georden@escapehither.com>
 */
class RequestHandlerUtils
{
    const ARGUMENTS = 'arguments';
    protected $request;
    /**
     * @return null|Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Transform a string from camel_case to underscore.
     *
     * @param string $input
     *
     * @return string A string lowercase with underscore pattern
     *
     */
    public static function fromCamelCase($input)
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

    /**
     * Get the root bundle
     *
     * @param array $paramController
     *
     * @return string
     */
    public static function getRootBundle($paramController)
    {
        $rootBundle = '';

        for ($i = 0; $i <= count($paramController) - 3; $i++) {
            $rootBundle .= $paramController[$i];
        }

        return $rootBundle;
    }

    /**
     * Get the root class
     *
     * @param array $paramController
     *
     * @return string
     */
    public static function getRootClass($paramController)
    {
        $rootClass = '';

        for ($i = 0; $i <= count($paramController) - 3; $i++) {
            $rootClass .= $paramController[$i].'\\';
        }

        return substr($rootClass, 0, -1);
    }

    /**
     *  Generate the resource name uses for the template.
     *
     * @param array $attributes The request attributes.
     *
     * @return string
     */
    public static function generateResourceViewName($attributes)
    {

        if ('indexAction' === $attributes['action']) {
            $name = $attributes['name'].'s';
        } else {
            $name = $attributes['name'];
        }

        return $name;
    }

    /**
     * Get info from the action
     *
     * @param array  $attributes The request attributes.
     * @param string $type       The info type.
     *
     * @return string
     */
    public static function getInfoFromAction(array $attributes, $type)
    {
        $actionList = ['index', 'new', 'show', 'edit'];
        $suffix = str_replace('Action', '', $attributes['action']);
        $info = null;

        if (in_array($suffix, $actionList)) {
            if ('path' === $type) {
                $info = $attributes['template'].'/'.$suffix.'.html.twig';
            } elseif ('route' === $type) {
                $info = $attributes['nameConfig'].'_'.$suffix;
            }
        }

        return $info;
    }

    /**
     * Get the attributes
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
            $attributes['rootClass'] = self::getRootClass($paramController);
            $attributes['rootBundle'] = self::getRootBundle($paramController);

            if (!empty($paramController)) {
                $attributes['bundle'] = $paramController[0];
            } else {
                $attributes['bundle'] = null;
            }

            $controller = $controllerActionTab[0];

            $attributes['resource'] = preg_replace('/Controller/', '', $controller);
            // The resource name available in template.
            $attributes['name'] = lcfirst($attributes['resource']);
            // The resource name for the template folder.
            $attributes['template'] = strtolower($attributes['resource']);
            // The resource name for getting config parameters.
            $attributes['nameConfig'] = self::fromCamelCase(
                $attributes['resource']
            );

            if (!empty($controllerActionTab[1])) {
                $attributes['action'] = $controllerActionTab[1];
            } else {
                $attributes['action'] = null;
            }

            $attributes['_route'] = $this->request->attributes->get('_route');
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

        if (isset($repositoryConfig[self::ARGUMENTS])) {
            foreach ($repositoryConfig[self::ARGUMENTS] as $key => $value) {
                $repositoryConfig[self::ARGUMENTS][$key] = $this->request->query->get(
                    $value
                );
            }
        } else {
            $repositoryConfig[self::ARGUMENTS] = null;
        }

        if (!isset($repositoryConfig['method'])) {
            $repositoryConfig['method'] = null;
        }

        return $repositoryConfig;
    }

    /**
     * Get factory config
     *
     * @return mixed
     */
    public function getFactoryConfig()
    {
        $factoryConfig = $this->request->attributes->get('factory');

        if (isset($factoryConfig[self::ARGUMENTS])) {
            foreach ($factoryConfig[self::ARGUMENTS] as $key => $value) {
                $factoryConfig[self::ARGUMENTS][$key] = $this->request->attributes->get(
                    $value
                );
            }
        }

        return $factoryConfig;
    }

    /**
     * Get The form Configuration.
     *
     * @return mixed
     */
    public function getFormConfig()
    {
        return $this->request->attributes->get('form');
    }

    /**
     *  Get The security Configuration.
     * @return mixed
     */
    public function getSecurityConfig()
    {
        return $this->request->attributes->get('security');
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

        if (isset($paramTemplate)) {
            return $paramTemplate;
        }

        $path = self::getInfoFromAction($attributes, 'path');

        if (isset($this->request->query) && $this->request->query->get('template')) {
            $path = $this->request->query->get('template');
        }

        return $path;
    }

    /**
     * Get the redirection route
     *
     * @param array $attributes
     *
     * @return string
     */
    public function generateReDirectionRoute(array $attributes)
    {

        $paramRedirectionRoute = $this->request->attributes->get('redirect');

        if (isset($paramRedirectionRoute)) {
            if (!empty($paramRedirectionRoute['route'])) {
                return $paramRedirectionRoute['route'];
            }

            return $paramRedirectionRoute;
        }

        $route = null;

        if ('indexAction' === $attributes['action'] || 'deleteAction' === $attributes['action']) {
            $route = $attributes['nameConfig'].'_index';
        } elseif ('newAction' === $attributes['action'] || 'editAction' === $attributes['action']) {
            $route = $attributes['nameConfig'].'_show';
        }

        return $route;
    }

    /**
     * Get route parameter
     *
     * @return mixed
     */
    public function getRouteParameter()
    {
        return $this->request->attributes->get('_route_params');
    }

    /**
     * Get the delete route from the routing parameter.
     *
     * @return string
     */
    public function generateDeleteRoute()
    {

        $paramDeleteRoute = $this->request->attributes->get('delete_route');

        if (isset($paramDeleteRoute)) {
            return $paramDeleteRoute;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getResourceClass()
    {
        $attributes = $this->getAttributes();

        return $attributes['rootClass'].'\Entity\\'.$attributes['resource'];
    }
}
