<?php

/**
 * This file is part of the Genia package.
 * (c) Georden Gaël LOUZAYADIO
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * Date: 01/07/17
 * Time: 23:02
 */
namespace EscapeHither\CrudManagerBundle\Tests;
use EscapeHither\CrudManagerBundle\EscapeHitherCrudManagerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
class EscapeHitherCrudManagerBundleTest extends \PHPUnit_Framework_TestCase{
    public function testBuild()
    {
        $container = $this->getMockBuilder(ContainerBuilder::class)
            ->setMethods(['addCompilerPass'])
            ->getMock();
        $container->expects($this->exactly(0))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf(CompilerPassInterface::class));
        $bundle = new EscapeHitherCrudManagerBundle();
        $bundle->build($container);
    }
}