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
        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')->getMock();


    }
    public function testBuild(){
        $attributes = [
            "_controller" => "OpenMarketPlace\ProductManagerBundle\Controller\ProductController::indexAction",
        ];
        $requestParameterHandler = $this->buildRequest($attributes);
        $requestParameterHandler->build();
        $this->assertEquals('indexAction', $requestParameterHandler->getActionName());
        $this->assertNotEmpty($requestParameterHandler->getAttributes());
    }
    
    protected function buildRequest($attributes){
        $request = new Request();
        $requestStack = new RequestStack();
        $container = $this->getMockBuilder(ContainerInterface::class)->getMock();
        $request->initialize([],[],$attributes);
        $requestStack->push($request);
        return new RequestParameterHandler($requestStack,$container);
    }

}