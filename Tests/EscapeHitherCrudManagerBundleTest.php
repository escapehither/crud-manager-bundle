<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Tests;

use EscapeHither\CrudManagerBundle\EscapeHitherCrudManagerBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 *  Crud manager Test
 *  @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class EscapeHitherCrudManagerBundleTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the bundle build
     */
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
