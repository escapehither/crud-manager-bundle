<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\ResourceProvider;

/**
 * The resource provider interface
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
interface ResourceProviderInterface
{
    public function getResult($request, $resourceClass, $format, $method = null, $methodArguments = null);
}
