<?php
/**
 * This file is part of the Escape Hither CRUD.
 * (c) Georden Gaël LOUZAYADIO <georden@escapehither.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace EscapeHither\CrudManagerBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Resource Interface.
 *
 * @author Georden Gaël LOUZAYADIO <georden@escapehither.com>
 */
interface ResourceInterface
{

    /**
     * Get Author
     *
     * @return UserInterface $author
     */
    public function getAuthor();

    /**
     * @param UserInterface $author
     */
    public function setAuthor(UserInterface $author);
}
