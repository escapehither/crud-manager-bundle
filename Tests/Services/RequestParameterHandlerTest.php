<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Tests\Services;

use EscapeHither\CrudManagerBundle\Services\RequestParameterHandler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Request parameter Handler Test
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class RequestParameterHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test Setup
     */
    public function setUp()
    {
    }

    /**
     * Test Build
     */
    public function testBuild()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('indexAction', $requestParameterHandler->getActionName());
        $this->assertNotEmpty($requestParameterHandler->getAttributes());
    }

    /**
     * Test Get action name
     */
    public function testGetActionName()
    {
        $actionList = [
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

        foreach ($actionList as $value) {
            $attributes = [
                "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::".$value,
            ];
            $requestParameterHandler = $this->buildRequest($attributes);
            $this->assertEquals($value, $requestParameterHandler->getActionName());
        }
    }

    /**
     * Test Get route Name
     */
    public function testGetRouteName()
    {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route' => 'product_index',
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product_index', $requestParameterHandler->getRouteName());
    }

    /**
     * Test Get resource Class
     */
    public function testGetResourceClass()
    {
        // TODO
    }

    /**
     * Test Generate Delete Route
     */
    public function testGenerateDeleteRoute()
    {
        //TODO
    }

    /**
     * Test Get Format
     *
     */
    public function testGetFormat()
    {
        //TODO
    }
    /**
     * @return string
     */
    public function testGetRedirectionRoute()
    {
    }

    /**
     * Build the request parameter Handler
     *
     * @param array $attributes
     * @return RequestParameterHandler
     */
    protected function buildRequest($attributes)
    {
        $requestStack = new RequestStack();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $request = new Request();
        $request->initialize([], [], $attributes);
        $requestStack->push($request);
        $requestParameterHandler = new RequestParameterHandler($requestStack, $container);
        $requestParameterHandler->build();

        return $requestParameterHandler;
    }
}
