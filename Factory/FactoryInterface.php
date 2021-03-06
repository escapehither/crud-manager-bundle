<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Factory;

/**
 * The factory interface
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
interface FactoryInterface
{

    /**
     * Create new resource
     *
     * @return Resource
     */
    public function create();
}
