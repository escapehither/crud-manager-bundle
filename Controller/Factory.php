<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace EscapeHither\CrudManagerBundle\Controller;

use EscapeHither\CrudManagerBundle\Entity\ResourceInterface;

/**
 * Class Factory
 * The default Factory used to create any Resource
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
class Factory
{
    /**
     *Create a new resource
     *
     * @param string $className the required class of your resource
     *
     *
     * @return ResourceInterface
     */
    public static function create($className)
    {
        return new $className();
    }
}
