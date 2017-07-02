<?php

/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 28/01/17
 * Time: 17:37
 */
namespace EscapeHither\CrudManagerBundle\Tests\Services;
use EscapeHither\CrudManagerBundle\Services\RequestParameterHandler;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

class RequestParameterHandlerTest extends \PHPUnit_Framework_TestCase
{

    public function setUp()
    {


    }
    public function testBuild(){
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('indexAction', $requestParameterHandler->getActionName());
        $this->assertNotEmpty($requestParameterHandler->getAttributes());
    }
    public function testGetActionName(){
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
        foreach($action_list as $value){
            $attributes = [
                "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::".$value,
            ];
            $requestParameterHandler = $this->buildRequest($attributes);
            $this->assertEquals($value, $requestParameterHandler->getActionName());

        }

    }
    public function testGetRouteName() {
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
            '_route'=>'product_index'
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $this->assertEquals('product_index', $requestParameterHandler->getRouteName());

    }
    public function testGetResourceClass(){

    }
    public function testGenerateDeleteRoute() {

    }
    public function testGetFormat() {

    }
    /**
     * @return string
     */
    public function testGetRedirectionRoute() {

    }

    protected function buildRequest($attributes){
        $request = new Request();
        $requestStack = new RequestStack();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $request->initialize([],[],$attributes);
        $requestStack->push($request);
        $requestParameterHandler= new RequestParameterHandler($requestStack,$container);
        $requestParameterHandler->build();
        return $requestParameterHandler;
    }

}