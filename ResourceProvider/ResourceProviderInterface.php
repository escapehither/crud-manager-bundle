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
    /**
     * Get the resource provider result.
     *
     * @param Request  $request         The request.
     * @param string   $resourceClass   The resource class.
     * @param string   $format          The format.
     * @param callable $method          The method.
     * @param mixed    $methodArguments The method arguments.
     *
     * @return mixed
     */
    public function getResult($request, $resourceClass, $format, $method = null, $methodArguments = null);
}